<?php

namespace Tests\Feature;

use App\Models\Promotion;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfferCardTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): void
    {
        $this->actingAs(User::create(['name' => 'Admin', 'email' => 'offers@example.test', 'password' => 'password', 'role' => 'superadmin', 'is_active' => true]));
    }

    private function data(array $overrides = []): array
    {
        return array_merge(['title' => 'Weekend stay', 'is_active' => 1, 'sort_order' => 0, 'button_text' => 'Enquire'], $overrides);
    }

    private function image(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('offer.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
    }

    public function test_only_active_cards_appear_in_order_on_public_pages(): void
    {
        $this->get('/')->assertOk()->assertDontSee('id="promotion-dialog"', false);
        Promotion::create($this->data(['title' => 'Last promotion', 'sort_order' => 10]));
        Promotion::create($this->data(['title' => 'Hidden promotion', 'is_active' => 0]));
        Promotion::create($this->data(['title' => 'First promotion']));
        foreach (['/', '/blogs'] as $page) {
            $this->get($page)->assertOk()->assertSee('id="promotion-dialog"', false)
                ->assertSeeInOrder(['First promotion', 'Last promotion'])->assertDontSee('Hidden promotion');
        }
        $this->withSession(['enquiry_success' => true])->get('/')->assertDontSee('id="promotion-dialog"', false);
    }

    public function test_admin_can_manage_multiple_cards(): void
    {
        $this->admin();
        $this->get(route('admin.promotions.create'))->assertOk();
        $this->post(route('admin.promotions.store'), $this->data())->assertSessionHasNoErrors()->assertRedirect(route('admin.promotions.index'));
        $this->post(route('admin.promotions.store'), $this->data(['title' => 'Family stay']))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('promotions', 2);
        $promotion = Promotion::firstOrFail();
        $this->get(route('admin.promotions.edit', $promotion))->assertOk()->assertSee('Weekend stay');
        $this->put(route('admin.promotions.update', $promotion), $this->data(['title' => 'Updated stay', 'is_active' => 0, 'sort_order' => 8]))->assertSessionHasNoErrors();
        $this->assertFalse($promotion->fresh()->is_active);
        $this->get('/')->assertDontSee('Updated stay')->assertSee('Family stay');
        $this->delete(route('admin.promotions.destroy', $promotion))->assertRedirect();
        $this->assertDatabaseCount('promotions', 1);
    }

    public function test_image_upload_retention_replacement_removal_and_deletion(): void
    {
        Storage::fake('public');
        $this->admin();
        $this->post(route('admin.promotions.store'), $this->data(['offer_image' => $this->image()]))->assertSessionHasNoErrors();
        $promotion = Promotion::firstOrFail();
        $path = $promotion->image_path;
        Storage::disk('public')->assertExists($path);
        $this->put(route('admin.promotions.update', $promotion), $this->data())->assertSessionHasNoErrors();
        $this->assertSame($path, $promotion->fresh()->image_path);
        $this->put(route('admin.promotions.update', $promotion), $this->data(['offer_image' => $this->image()]))->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($path);
        $replacement = $promotion->fresh()->image_path;
        Storage::disk('public')->assertExists($replacement);
        $this->put(route('admin.promotions.update', $promotion), $this->data(['remove_offer_image' => 1]))->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($replacement);
        $this->assertNull($promotion->fresh()->image_path);
        $this->put(route('admin.promotions.update', $promotion), $this->data(['offer_image' => $this->image()]))->assertSessionHasNoErrors();
        $path = $promotion->fresh()->image_path;
        $this->delete(route('admin.promotions.destroy', $promotion))->assertRedirect();
        Storage::disk('public')->assertMissing($path);
    }

    public function test_invalid_links_and_uploads_are_rejected_before_saving(): void
    {
        Storage::fake('public');
        $this->admin();
        foreach (['javascript:alert(1)', '//example.com', '/\\example.com', 'data:text/html,hello'] as $url) {
            $this->post(route('admin.promotions.store'), $this->data(['button_url' => $url]))->assertSessionHasErrors('button_url');
        }
        $this->post(route('admin.promotions.store'), $this->data(['title' => '']))->assertSessionHasErrors('title');
        $this->post(route('admin.promotions.store'), $this->data(['offer_image' => UploadedFile::fake()->createWithContent('test.svg', '<svg/>')]))->assertSessionHasErrors('offer_image');
        $this->assertDatabaseCount('promotions', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_guests_cannot_manage_promotions(): void
    {
        $this->post(route('admin.promotions.store'), $this->data())->assertRedirect(route('login'));
        $this->get(route('admin.promotions.index'))->assertRedirect(route('login'));
        $this->assertDatabaseCount('promotions', 0);
    }

    public function test_migration_preserves_the_existing_offer_and_image(): void
    {
        foreach (['offer_title' => 'Existing offer', 'offer_image' => 'offers/existing.jpg', 'offer_active' => '1'] as $key => $value) {
            SiteSetting::create(['key' => $key, 'value' => $value, 'group' => 'offers', 'type' => 'text', 'label' => $key]);
        }
        Schema::drop('promotions');
        $migration = require database_path('migrations/2026_09_12_100000_create_promotions_table.php');
        $migration->up();
        $expiryMigration = require database_path('migrations/2026_09_12_110000_add_end_date_to_promotions_table.php');
        $expiryMigration->up();
        $this->assertSame('Existing offer', Promotion::firstOrFail()->title);
        $this->assertSame('offers/existing.jpg', Promotion::firstOrFail()->image_path);
        $this->assertSame('Existing offer', SiteSetting::get('offer_title'));
    }
}
