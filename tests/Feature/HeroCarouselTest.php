<?php

namespace Tests\Feature;

use App\Models\HeroSlide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HeroCarouselTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_visible_slides_appear_in_order(): void
    {
        foreach ([['Last', 20, true], ['Hidden', 0, false], ['First', 1, true]] as [$title, $order, $active]) {
            HeroSlide::create(['title' => $title, 'media_path' => "$title.jpg", 'media_type' => 'image', 'sort_order' => $order, 'is_active' => $active]);
        }
        $this->get('/')->assertOk()->assertSeeInOrder(['First.jpg', 'Last.jpg'])->assertDontSee('Hidden.jpg');
    }

    public function test_admin_can_upload_replace_hide_and_delete_media(): void
    {
        Storage::fake('public');
        $admin = User::create(['name' => 'Admin', 'email' => 'carousel@example.com', 'password' => 'password', 'role' => 'superadmin', 'is_active' => true]);
        $this->actingAs($admin)->post(route('admin.hero-slides.store'), [
            'title' => 'Hotel', 'sort_order' => 0, 'is_active' => 1,
            'media' => UploadedFile::fake()->create('hotel.mp4', 100, 'video/mp4'),
        ])->assertRedirect(route('admin.hero-slides.index'));
        $slide = HeroSlide::firstOrFail();
        $this->assertSame('video', $slide->media_type);
        $oldPath = $slide->media_path;
        Storage::disk('public')->assertExists($oldPath);
        $this->get('/')->assertOk()->assertSee('<video', false)->assertSee($slide->media_url);
        $this->put(route('admin.hero-slides.update', $slide), [
            'title' => 'Updated hotel', 'sort_order' => 2, 'is_active' => 0,
            'media' => UploadedFile::fake()->create('hotel.jpg', 100, 'image/jpeg'),
        ])->assertRedirect(route('admin.hero-slides.index'));
        $slide->refresh();
        $this->assertSame('image', $slide->media_type);
        $this->assertFalse($slide->is_active);
        Storage::disk('public')->assertMissing($oldPath);
        $this->delete(route('admin.hero-slides.destroy', $slide))->assertRedirect();
        Storage::disk('public')->assertMissing($slide->media_path);
        $this->assertDatabaseCount('hero_slides', 0);
    }

    public function test_invalid_media_is_rejected_and_guests_cannot_manage_slides(): void
    {
        $this->get(route('admin.hero-slides.index'))->assertRedirect(route('login'));
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password', 'role' => 'superadmin', 'is_active' => true]);
        $this->actingAs($admin)->post(route('admin.hero-slides.store'), [
            'title' => 'Invalid', 'sort_order' => 0, 'is_active' => 1,
            'media' => UploadedFile::fake()->create('script.svg', 1, 'image/svg+xml'),
        ])->assertSessionHasErrors('media');
        $this->assertDatabaseCount('hero_slides', 0);
    }
}
