<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Room;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\DisplayCurrency;
use App\Services\VisitorCountry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisitorCurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['currency.cache_store' => 'array', 'currency.geoip_enabled' => true]);
        Cache::store('array')->flush();
        Http::preventStrayRequests();
    }

    private function rates(): void
    {
        foreach (['currency_npr_per_inr' => '1.6', 'currency_npr_per_usd' => '160'] as $key => $value) {
            SiteSetting::create(['key' => $key, 'value' => $value, 'type' => 'number', 'group' => 'currency', 'label' => $key]);
        }
    }

    public function test_location_choice_saves_a_year_long_currency_cookie(): void
    {
        $this->get('/')->assertSee('Where are you visiting from?');
        foreach (['NP' => 'NPR', 'IN' => 'INR', 'OTHER' => 'USD'] as $country => $code) {
            $response = $this->post(route('currency.store'), ['country' => $country, 'return_to' => '/blogs']);
            $response->assertRedirect('/blogs')->assertCookie('display_currency', $code);
            $cookie = $response->getCookie('display_currency');
            $this->assertEqualsWithDelta(now()->addYear()->timestamp, $cookie->getExpiresTime(), 86400);
            $this->assertTrue($cookie->isHttpOnly());
            $this->assertSame('lax', $cookie->getSameSite());
        }
    }

    public function test_manual_currency_overrides_location_and_persists_on_public_pages(): void
    {
        $this->rates();
        Room::create(['name' => 'Saved Currency Room', 'category' => 'deluxe', 'price_per_night' => 1600, 'is_active' => true]);
        $this->post(route('currency.store'), ['currency' => 'INR'])->assertCookie('display_currency', 'INR');
        $this->withCookie('display_currency', 'INR')->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->get('/')->assertOk()->assertSee('INR 1,000.00')->assertDontSee('Where are you visiting from?');
        $this->get('/blogs')->assertOk()->assertDontSee('Where are you visiting from?');
        Http::assertNothingSent();
        $this->assertSame('1600.00', Room::first()->price_per_night);
    }

    public function test_invalid_preferences_and_external_redirects_are_rejected(): void
    {
        foreach ([[], ['currency' => 'EUR'], ['country' => 'bad'], ['currency' => ['USD']],
            ['currency' => 'USD', 'return_to' => '//example.com']] as $payload) {
            $this->postJson(route('currency.store'), $payload)->assertUnprocessable()->assertCookieMissing('display_currency');
        }
        $this->withCookie('display_currency', 'EUR')->get('/')->assertOk()->assertSee('Where are you visiting from?');
    }

    public function test_country_selects_currency_for_rooms_packages_services_and_details(): void
    {
        $this->rates();
        Room::create(['name' => 'Currency Room', 'category' => 'deluxe', 'price_per_night' => 1600, 'is_active' => true]);
        Package::create(['name' => 'Currency Package', 'price_label' => 'NPR 3,200 / person', 'is_active' => true]);
        Service::create(['title' => 'Transfer', 'price_label' => 'NPR 160 / trip', 'is_active' => true]);
        foreach ([['8.8.8.8', 'IN', 'INR 1,000.00', 'INR 2,000.00 / person', 'INR 100.00 / trip'],
                  ['1.1.1.1', 'NP', 'NPR 1,600', 'NPR 3,200 / person', 'NPR 160 / trip'],
                  ['9.9.9.9', 'JP', 'USD 10.00', 'USD 20.00 / person', 'USD 1.00 / trip']] as [$ip, $country, $room, $package, $service]) {
            Http::fake(['get.geojs.io/v1/ip/country/'.$ip.'.json' => Http::response(['country' => $country])]);
            $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])->get('/');
            $response->assertOk()->assertSee($room)->assertSee($package)->assertSee($service);
            $this->assertSame(2, substr_count($response->getContent(), $package));
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        }
        $this->assertSame('1600.00', Room::first()->price_per_night);
    }

    public function test_country_lookup_is_cached_and_untrusted_headers_are_ignored(): void
    {
        Http::fake(['*' => Http::response(['country' => 'IN'])]);
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '8.8.8.8', 'HTTP_CF_IPCOUNTRY' => 'NP', 'HTTP_X_FORWARDED_FOR' => '1.1.1.1']);
        config(['currency.country_header' => 'CF-IPCountry']);
        $detector = app(VisitorCountry::class);
        $this->assertSame('IN', $detector->detect($request));
        $this->assertSame('IN', $detector->detect($request));
        Http::assertSentCount(1);
        Http::assertSent(fn ($r) => str_contains($r->url(), '/8.8.8.8.json'));
    }

    public function test_private_preview_uses_npr_without_external_lookup(): void
    {
        $this->assertSame('NP', app(VisitorCountry::class)->detect(Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.1'])));
        $this->get('/')->assertOk();
        Http::assertNothingSent();
    }

    public function test_lookup_failure_falls_back_to_usd_and_is_temporarily_cached(): void
    {
        $this->rates();
        Room::create(['name' => 'Fallback Room', 'category' => 'deluxe', 'price_per_night' => 1600, 'is_active' => true]);
        Http::fake(['*' => Http::response([], 503)]);
        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->get('/')->assertOk()->assertSee('USD 10.00');
        $this->get('/')->assertOk()->assertSee('USD 10.00');
        Http::assertSentCount(1);
    }

    public function test_settings_save_validate_and_immediately_affect_prices(): void
    {
        $this->actingAs(User::create(['name' => 'Admin', 'email' => 'currency@example.test', 'password' => 'password', 'role' => 'superadmin', 'is_active' => true]));
        $this->get(route('admin.settings.index'))->assertOk()->assertSee('1 INR = how many NPR?')->assertSee('1 USD = how many NPR?');
        $this->post(route('admin.settings.update'), ['currency_npr_per_inr' => '1.6', 'currency_npr_per_usd' => '160', '_settings_tab' => 'currency'])->assertSessionHasNoErrors()->assertSessionHas('settings_tab', 'currency');
        $this->assertSame('160', SiteSetting::get('currency_npr_per_usd'));
        foreach (['0', '-1', 'bad', '', '1000001', ['160']] as $invalid) {
            $this->post(route('admin.settings.update'), ['currency_npr_per_inr' => '2', 'currency_npr_per_usd' => $invalid])->assertSessionHasErrors('currency_npr_per_usd');
            $this->assertSame('1.6', SiteSetting::get('currency_npr_per_inr'));
        }
        Room::create(['name' => 'Updated Rate Room', 'category' => 'deluxe', 'price_per_night' => 1600, 'is_active' => true]);
        Http::fake(['*' => Http::response(['country' => 'US'])]);
        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->get('/')->assertSee('USD 10.00');
        $this->post(route('admin.settings.update'), ['currency_npr_per_usd' => '200'])->assertSessionHasNoErrors();
        $this->get('/')->assertSee('USD 8.00');
    }

    public function test_price_labels_preserve_units_ranges_and_non_price_numbers(): void
    {
        $currency = new DisplayCurrency('INR', ['NPR' => 1, 'INR' => 1.6, 'USD' => 160]);
        foreach ([
            ['NPR 1,600 / person', 'INR 1,000.00 / person'],
            ['From 1,600 / night', 'From INR 1,000.00 / night'],
            ['1,600 / couple', 'INR 1,000.00 / couple'],
            ['USD 10 / person', 'INR 1,000.00 / person'],
            ['₹1,000', 'INR 1,000.00'],
            ['NPR 1,600–3,200 / night', 'INR 1,000.00–2,000.00 / night'],
            ['2 nights from 1,600 / person', '2 nights from INR 1,000.00 / person'],
            ['Price on request', 'Price on request'], ['20% off for 2 nights', '20% off for 2 nights'],
            ['EUR 100', 'EUR 100'],
        ] as [$input, $expected]) $this->assertSame($expected, $currency->label($input));
        $this->assertSame('From INR 1,000.00', $currency->label(null, 1600));
        $this->assertSame('From INR 0.00', $currency->label(null, 0));
    }
}
