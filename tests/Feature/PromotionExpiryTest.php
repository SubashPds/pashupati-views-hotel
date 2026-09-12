<?php

namespace Tests\Feature;

use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PromotionExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_expiry_uses_nepal_midnight_and_applies_to_all_public_pages(): void
    {
        $this->travelTo(Carbon::parse('2026-09-12 18:14:59', 'UTC'));
        $ending = Promotion::create(['title' => 'Ends tonight', 'is_active' => true, 'end_date' => '2026-09-12']);
        Promotion::create(['title' => 'Expired yesterday', 'is_active' => true, 'end_date' => '2026-09-11']);
        Promotion::create(['title' => 'No deadline', 'is_active' => true]);
        Promotion::create(['title' => 'Future offer', 'is_active' => true, 'end_date' => '2026-09-20']);
        Promotion::create(['title' => 'Inactive offer', 'is_active' => false]);

        $this->assertFalse($ending->is_expired);
        foreach (['/', '/blogs'] as $page) {
            $this->get($page)->assertOk()->assertSee('Ends tonight')->assertSee('No deadline')
                ->assertSee('Future offer')->assertDontSee('Expired yesterday')->assertDontSee('Inactive offer');
        }

        $this->travelTo(Carbon::parse('2026-09-12 18:15:00', 'UTC'));
        $this->assertTrue($ending->is_expired);
        $this->assertTrue($ending->fresh()->is_active);
        foreach (['/', '/blogs'] as $page) {
            $this->get($page)->assertOk()->assertDontSee('Ends tonight')->assertSee('No deadline')->assertSee('Future offer');
        }
    }

    public function test_no_popup_is_rendered_when_all_promotions_are_expired(): void
    {
        $this->travelTo(Carbon::parse('2026-09-13 00:00:00', Promotion::TIMEZONE));
        Promotion::create(['title' => 'Finished offer', 'is_active' => true, 'end_date' => '2026-09-12']);
        $this->get('/')->assertOk()->assertDontSee('id="promotion-dialog"', false);
    }

    public function test_admin_can_save_change_and_clear_deadlines_and_see_expired_status(): void
    {
        $this->travelTo(Carbon::parse('2026-09-13 00:00:00', Promotion::TIMEZONE));
        $this->actingAs(User::create(['name' => 'Admin', 'email' => 'expiry@example.test', 'password' => 'password', 'role' => 'superadmin', 'is_active' => true]));
        $data = ['title' => 'Scheduled offer', 'is_active' => 1, 'sort_order' => 0, 'end_date' => '2026-09-12'];
        $this->post(route('admin.promotions.store'), $data)->assertSessionHasNoErrors()->assertRedirect();
        $promotion = Promotion::firstOrFail();
        $this->assertSame('2026-09-12', $promotion->end_date->toDateString());
        $this->get(route('admin.promotions.edit', $promotion))->assertOk()->assertSee('value="2026-09-12"', false);
        $this->get(route('admin.promotions.index'))->assertOk()->assertSee('Expired')->assertSee('Ends 12 Sep 2026');

        $this->put(route('admin.promotions.update', $promotion), array_replace($data, ['end_date' => '2026-09-30']))->assertSessionHasNoErrors();
        $this->assertSame('2026-09-30', $promotion->fresh()->end_date->toDateString());
        $this->assertFalse($promotion->fresh()->is_expired);
        $this->put(route('admin.promotions.update', $promotion), array_replace($data, ['end_date' => '']))->assertSessionHasNoErrors();
        $this->assertNull($promotion->fresh()->end_date);

        foreach (['2026-02-30', 'not-a-date', '12/09/2026'] as $invalid) {
            $this->put(route('admin.promotions.update', $promotion), array_replace($data, ['end_date' => $invalid]))->assertSessionHasErrors('end_date');
            $this->assertNull($promotion->fresh()->end_date);
        }
    }
}
