<?php

namespace Tests\Unit;

use App\Rules\StrongPassword;
use App\Support\SafeHtml;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    public function test_rich_text_drops_encoded_scripts_event_handlers_and_dom_clobbering(): void
    {
        $payloads = [
            '<a href="jav&#x61;script:alert(1)" onclick="alert(2)">Link</a>',
            '<a href="java&#10;script:alert(1)">Link</a>',
            '<img src="data:image/svg+xml,evil" onerror=alert(1)>',
            '<svg><a xlink:href="javascript:alert(1)">SVG</a></svg>',
            '<math><mtext><img src=x onerror=alert(1)></mtext></math>',
            '<script>alert(1)</script><iframe srcdoc="evil"></iframe>',
            '<div id="gallery-viewer" name="openBooking" style="position:fixed" data-open-booking>Text</div>',
            '<a href="//evil.example/path">Link</a><img src="/\\evil.example/image.jpg">',
        ];
        foreach ($payloads as $html) {
            $safe = SafeHtml::clean($html);
            $document = new \DOMDocument;
            $document->loadHTML('<html><body>'.$safe.'</body></html>', LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new \DOMXPath($document);
            $this->assertSame(0, $xpath->query('//script|//svg|//math|//iframe|//@onclick|//@onerror|//@style|//@id|//@name|//@data-open-booking|//@srcdoc')->length, $safe);
            foreach ($xpath->query('//@href|//@src') as $attribute) {
                $this->assertDoesNotMatchRegularExpression('/javascript:|data:|evil\.example/i', $attribute->value);
            }
        }
    }

    public function test_rich_text_preserves_safe_formatting_and_links(): void
    {
        $safe = SafeHtml::clean('<h2>Stay &amp; relax</h2><p><strong>Welcome</strong> नेपाली</p><a href="https://example.com" target="_blank">Visit</a><img src="/storage/rooms/a.jpg" alt="Room" width="400"><table><tr><td colspan="2">Details</td></tr></table>');
        $this->assertStringContainsString('<strong>Welcome</strong>', $safe);
        $this->assertStringContainsString('नेपाली', $safe);
        $this->assertStringContainsString('href="https://example.com" target="_blank" rel="noopener noreferrer"', $safe);
        $this->assertStringContainsString('src="/storage/rooms/a.jpg"', $safe);
        $this->assertStringContainsString('colspan="2"', $safe);
    }

    public function test_new_passwords_support_long_passphrases_without_bcrypt_truncation(): void
    {
        foreach (['short', str_repeat('a', 14), str_repeat('a', 73), str_repeat('界', 25)] as $password) {
            $this->assertTrue(Validator::make(compact('password'), ['password' => ['required', new StrongPassword]])->fails());
        }
        foreach (['a longer unique passphrase', str_repeat('a', 72), str_repeat('界', 15)] as $password) {
            $this->assertFalse(Validator::make(compact('password'), ['password' => ['required', new StrongPassword]])->fails());
        }
    }

    public function test_security_headers_and_unique_nonces_match_trusted_script_tags(): void
    {
        Route::get('/security-probe', fn () => response(Blade::render('<script nonce="{{ Vite::cspNonce() }}">window.safe = true;</script>')));
        $response = $this->get('/security-probe')->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeaderMissing('Strict-Transport-Security');
        $nonce = Vite::cspNonce();
        $response->assertSee('nonce="'.$nonce.'"', false);
        $policy = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("'nonce-{$nonce}'", $policy);
        $this->assertStringContainsString("script-src-attr 'none'", $policy);
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
        $this->get('/security-probe')->assertOk();
        $this->assertNotSame($nonce, Vite::cspNonce());
        $this->get('https://localhost/security-probe')->assertOk()->assertHeader('Strict-Transport-Security', 'max-age=31536000');
        $this->get('/missing-security-route')->assertNotFound()->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_admin_responses_cannot_be_stored_in_shared_caches(): void
    {
        Route::get('/admin/security-probe', fn () => response('private'));
        $response = $this->get('/admin/security-probe')->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_login_account_limit_survives_changes_of_ip_and_email_case(): void
    {
        Route::post('/security-login-probe', fn () => response('attempt'))->middleware('throttle:login-account');
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.'.($attempt + 1)])
                ->post('/security-login-probe', ['email' => $attempt % 2 ? ' Guest@example.test ' : 'guest@example.test'])->assertOk();
        }
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.2'])
            ->postJson('/security-login-probe', ['email' => 'guest@example.test'])->assertStatus(429)->assertHeader('Retry-After');
        $this->post('/security-login-probe', ['email' => 'another@example.test'])->assertOk();
    }
}
