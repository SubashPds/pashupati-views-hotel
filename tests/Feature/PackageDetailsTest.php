<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PackageDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_only_loads_package_cards(): void
    {
        $package = Package::create([
            'name' => 'On Demand Package', 'price_from' => 1600,
            'description' => 'Detailed package description only available on demand.', 'highlights' => ['Private temple visit'], 'is_active' => true,
        ]);
        $package->images()->create(['image_path' => 'packages/gallery/on-demand.jpg', 'sort_order' => 1]);

        DB::enableQueryLog();
        $response = $this->get('/');
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertOk()->assertSee('On Demand Package')
            ->assertSee(route('packages.details', $package))
            ->assertDontSee($package->description)
            ->assertDontSee('Private temple visit')
            ->assertDontSee('packages/gallery/on-demand.jpg')
            ->assertDontSee('<dialog id="package-details-', false);
        foreach ($queries as $query) {
            $this->assertStringNotContainsString('package_images', $query['query']);
        }
        $this->assertFalse($response->viewData('packages')->first()->relationLoaded('images'));
        $this->assertArrayNotHasKey('description', $response->viewData('packages')->first()->getAttributes());
        $this->assertArrayNotHasKey('highlights', $response->viewData('packages')->first()->getAttributes());
    }

    public function test_details_include_requested_package_gallery_and_selected_currency(): void
    {
        $package = Package::create([
            'name' => 'Requested Package', 'price_from' => 1600,
            'description' => 'Full requested package description.', 'is_active' => true,
        ]);
        $package->images()->create(['image_path' => 'packages/gallery/requested.jpg', 'caption' => 'Balcony view', 'sort_order' => 1]);
        Package::create(['name' => 'Other Package', 'price_from' => 2000, 'is_active' => true]);

        $response = $this->withCookie('display_currency', 'INR')->get(route('packages.details', $package));
        $response->assertOk()->assertSee('Requested Package')->assertSee($package->description)
            ->assertSee('packages/gallery/requested.jpg')->assertSee('Balcony view')->assertSee('INR')
            ->assertSee('data-package-enquiry="Requested Package"', false)->assertDontSee('Other Package');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_contact_package_options_do_not_load_galleries(): void
    {
        $package = Package::create(['name' => 'Enquiry Package', 'price_from' => 1600, 'is_active' => true]);
        $package->images()->create(['image_path' => 'packages/gallery/contact.jpg', 'sort_order' => 1]);

        DB::enableQueryLog();
        $response = $this->get('/contact');
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertOk()->assertSee('Enquiry Package')->assertDontSee('packages/gallery/contact.jpg');
        foreach ($queries as $query) {
            $this->assertStringNotContainsString('package_images', $query['query']);
        }
    }

    public function test_inactive_and_missing_package_details_are_not_public(): void
    {
        $package = Package::create(['name' => 'Hidden Package', 'price_from' => 1600, 'is_active' => false]);
        $this->get(route('packages.details', $package))->assertNotFound()->assertDontSee('Hidden Package');
        $this->get(route('packages.details', 999999))->assertNotFound();
    }
}
