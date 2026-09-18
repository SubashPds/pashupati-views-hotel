<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\StaySettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_packages_button_requires_an_active_package(): void
    {
        $this->get('/')->assertOk()->assertSee('Your ideal hotel experience starts here.')->assertDontSee('View packages');
        $package = Package::create(['name' => 'Temple Tour', 'is_active' => false]);
        $this->get('/')->assertDontSee('View packages');
        $package->update(['is_active' => true]);
        $this->get('/')->assertSee('View packages');
    }

    public function test_admin_can_save_all_section_content_and_hide_the_section(): void
    {
        $this->actingAs(User::create(['name' => 'Admin', 'email' => 'stay@example.test', 'password' => 'password', 'role' => 'superadmin', 'is_active' => true]));
        $this->get(route('admin.settings.index'))->assertOk()->assertSee('Plan your stay')->assertSee('stay_card_3_description');
        $values = StaySettings::defaults();
        foreach ($values as $key => $value) {
            if ($key !== 'stay_enabled') $values[$key] = 'Updated '.$key;
        }
        $this->post(route('admin.settings.update'), $values + ['_settings_tab' => 'stay'])
            ->assertSessionHasNoErrors()->assertSessionHas('settings_tab', 'stay');
        Package::create(['name' => 'Temple Tour', 'is_active' => true]);
        $response = $this->get('/')->assertOk();
        foreach ($values as $key => $value) {
            $this->assertSame($value, SiteSetting::get($key));
            if ($key !== 'stay_enabled') $response->assertSee($value);
        }
        $this->post(route('admin.settings.update'), ['stay_enabled' => '0'])->assertSessionHasNoErrors();
        $this->get('/')->assertDontSee('Updated stay_title');
    }

    public function test_invalid_settings_are_rejected_before_saving(): void
    {
        $this->actingAs(User::create(['name' => 'Admin', 'email' => 'stay@example.test', 'password' => 'password', 'role' => 'superadmin', 'is_active' => true]));
        $this->post(route('admin.settings.update'), ['stay_title' => ['invalid'], 'stay_enabled' => 'yes'])
            ->assertSessionHasErrors(['stay_title', 'stay_enabled']);
        $this->assertDatabaseMissing('site_settings', ['key' => 'stay_title']);
    }
}
