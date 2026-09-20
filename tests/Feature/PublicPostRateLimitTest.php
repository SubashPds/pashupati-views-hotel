<?php

namespace Tests\Feature;

use App\Mail\NewEnquiry;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicPostRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_enquiry_limit_blocks_saves_and_email_and_cannot_be_reset_with_a_new_session(): void
    {
        Mail::fake();
        SiteSetting::create(['key' => 'contact_email', 'value' => 'hotel@example.test', 'type' => 'text', 'group' => 'contact', 'label' => 'Email']);
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.20']);
        $payload = ['guest_name' => 'Guest', 'email' => 'guest@example.test'];
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('enquire'), $payload)->assertCreated();
        }
        $this->flushSession();
        $response = $this->withHeader('X-Forwarded-For', '203.0.113.99')
            ->postJson(route('enquire'), array_replace($payload, ['email' => 'another@example.test']));
        $response->assertStatus(429)->assertHeader('Retry-After')->assertJsonStructure(['message', 'retry_after']);
        $this->assertGreaterThan(0, $response->json('retry_after'));
        $this->assertDatabaseCount('enquiries', 5);
        Mail::assertSent(NewEnquiry::class, 5);

        // Another visitor still has an independent quota.
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.21'])
            ->postJson(route('enquire'), $payload)->assertCreated();
        $this->assertDatabaseCount('enquiries', 6);
    }

    public function test_failed_validation_counts_and_minute_window_recovers(): void
    {
        Mail::fake();
        $this->freezeTime();
        for ($i = 0; $i < 5; $i++) $this->postJson(route('enquire'), [])->assertUnprocessable();
        $this->postJson(route('enquire'), [])->assertStatus(429);
        $this->travel(61)->seconds();
        $this->postJson(route('enquire'), [])->assertUnprocessable();
        $this->assertDatabaseCount('enquiries', 0);
        Mail::assertNothingSent();
    }

    public function test_hourly_enquiry_limit_survives_minute_resets(): void
    {
        Mail::fake();
        $this->freezeTime();
        for ($minute = 0; $minute < 4; $minute++) {
            for ($attempt = 0; $attempt < 5; $attempt++) $this->postJson(route('enquire'), [])->assertUnprocessable();
            $this->travel(61)->seconds();
        }
        $response = $this->postJson(route('enquire'), [])->assertStatus(429);
        $this->assertGreaterThan(60, $response->json('retry_after'));
        $this->travel(1)->hours();
        $this->postJson(route('enquire'), [])->assertUnprocessable();
        $this->assertDatabaseCount('enquiries', 0);
        Mail::assertNothingSent();
    }

    public function test_country_and_currency_share_a_limit_separate_from_enquiries(): void
    {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $payload = $attempt % 2 ? ['country' => 'IN'] : ['currency' => 'USD'];
            $this->postJson(route('currency.store'), $payload)->assertOk();
        }
        $this->postJson(route('currency.store'), ['currency' => 'NPR'])->assertStatus(429)->assertCookieMissing('display_currency');
        $this->postJson(route('enquire'), [])->assertUnprocessable();
        $this->get('/contact')->assertOk();
    }

    public function test_login_attempts_are_limited_even_if_the_email_changes(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.post'), ['email' => "guest{$attempt}@example.test", 'password' => 'wrong'])
                ->assertSessionHasErrors('email');
        }
        $this->postJson(route('login.post'), ['email' => 'different@example.test', 'password' => 'wrong'])->assertStatus(429);
        $this->get(route('login'))->assertOk();
        $this->assertGuest();
    }

    public function test_non_ajax_visitors_get_a_readable_429_with_retry_headers(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) $this->postJson(route('enquire'), [])->assertUnprocessable();
        $this->post(route('enquire'), [])->assertStatus(429)
            ->assertHeader('Retry-After')->assertSee('Please wait before trying again');
    }
}
