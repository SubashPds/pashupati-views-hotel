<?php

namespace Tests\Feature;

use App\Models\GalleryItem;
use App\Models\Package;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DashboardQaRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs(User::create(['name'=>'QA Admin', 'email'=>'qa-regression@example.test', 'password'=>'password', 'role'=>'superadmin', 'is_active'=>true]));
    }

    private function roomData(array $overrides = []): array
    {
        return array_merge(['name'=>'Garden Room', 'category'=>'deluxe', 'price_per_night'=>'1200.50', 'max_guests'=>2, 'is_active'=>1, 'sort_order'=>0, 'amenities_raw'=>''], $overrides);
    }

    private function image(string $name = 'photo.png', bool $oversize = false): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');
        return UploadedFile::fake()->createWithContent($name, $bytes.($oversize ? str_repeat('x', 4 * 1024 * 1024) : ''));
    }

    public function test_optional_amenities_save_and_update_as_empty_arrays(): void
    {
        $this->post(route('admin.rooms.store'), $this->roomData())->assertRedirect(route('admin.rooms.index'));
        $room = Room::firstOrFail();
        $this->assertSame([], $room->amenities);
        $this->put(route('admin.rooms.update', $room), $this->roomData(['amenities_raw'=>"Wi-Fi\n\n Balcony "]))->assertRedirect();
        $this->assertSame(['Wi-Fi', 'Balcony'], $room->fresh()->amenities);
        $this->put(route('admin.rooms.update', $room), $this->roomData(['amenities_raw'=>null]))->assertRedirect();
        $this->assertSame([], $room->fresh()->amenities);
    }

    public function test_duplicate_normalized_name_is_rejected_before_uploading_files(): void
    {
        $this->post(route('admin.rooms.store'), $this->roomData())->assertRedirect();
        $this->post(route('admin.rooms.store'), $this->roomData(['name'=>'Garden-Room', 'cover_image'=>$this->image()]))->assertSessionHasErrors('name');
        $this->assertDatabaseCount('rooms', 1);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_invalid_gallery_file_rejects_entire_create_and_update(): void
    {
        $invalid = fn () => UploadedFile::fake()->createWithContent('notes.txt', 'Not an image');
        $this->post(route('admin.rooms.store'), $this->roomData(['gallery_images'=>[$this->image(), $invalid()]]))->assertSessionHasErrors('gallery_images.1');
        $this->assertDatabaseCount('rooms', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->post(route('admin.rooms.store'), $this->roomData(['cover_image'=>$this->image()]))->assertRedirect();
        $room = Room::firstOrFail();
        $original = $room->cover_image;
        $this->put(route('admin.rooms.update', $room), $this->roomData(['name'=>'Changed', 'cover_image'=>$this->image('replacement.png'), 'gallery_images'=>[$invalid()]]))->assertSessionHasErrors('gallery_images.0');
        $this->assertSame('Garden Room', $room->fresh()->name);
        $this->assertSame([$original], Storage::disk('public')->allFiles());
    }

    public function test_gallery_limits_and_valid_multiple_images(): void
    {
        $this->post(route('admin.rooms.store'), $this->roomData(['gallery_images'=>[$this->image('big.png', true)]]))->assertSessionHasErrors('gallery_images.0');
        $this->post(route('admin.rooms.store'), $this->roomData(['gallery_images'=>'not-an-array']))->assertSessionHasErrors('gallery_images');
        $this->assertDatabaseCount('rooms', 0);
        $this->post(route('admin.rooms.store'), $this->roomData(['gallery_images'=>[$this->image(), $this->image('second.png')]]))->assertRedirect();
        $this->assertCount(2, Room::firstOrFail()->images);
        $this->assertCount(2, Storage::disk('public')->allFiles());
    }

    public function test_package_guest_limits_keep_unlimited_maximum_supported(): void
    {
        $this->post(route('admin.packages.store'), ['name'=>'Invalid range', 'min_guests'=>5, 'max_guests'=>2])->assertSessionHasErrors('max_guests');
        $this->assertDatabaseCount('packages', 0);
        $this->post(route('admin.packages.store'), ['name'=>'Unlimited', 'min_guests'=>5, 'max_guests'=>''])->assertRedirect();
        $package = Package::firstOrFail();
        $this->assertNull($package->max_guests);
        $this->put(route('admin.packages.update', $package), ['name'=>'Unlimited', 'min_guests'=>5, 'max_guests'=>2])->assertSessionHasErrors('max_guests');
        $this->assertNull($package->fresh()->max_guests);
        $this->put(route('admin.packages.update', $package), ['name'=>'Equal range', 'min_guests'=>5, 'max_guests'=>5])->assertRedirect();
    }

    public function test_gallery_metadata_can_be_edited_without_replacing_media(): void
    {
        Storage::disk('public')->put('gallery/existing.png', 'Original media');
        $item = GalleryItem::create(['image_path'=>'gallery/existing.png', 'title'=>'Before', 'section'=>'general', 'sort_order'=>0, 'is_active'=>true]);
        $this->get(route('admin.gallery.edit', $item))->assertOk()->assertSee('Before');
        $this->put(route('admin.gallery.update', $item), ['title'=>'After', 'badge_label'=>'Dining', 'section'=>'dining', 'sort_order'=>4])->assertRedirect(route('admin.gallery.index'));
        $this->assertSame('After', $item->fresh()->title);
        $this->assertSame('dining', $item->fresh()->section);
        $this->assertSame('gallery/existing.png', $item->fresh()->image_path);
        $this->assertSame('Original media', Storage::disk('public')->get($item->image_path));
        $this->put(route('admin.gallery.update', $item), ['sort_order'=>-1])->assertSessionHasErrors('sort_order');
        $this->assertSame(4, $item->fresh()->sort_order);
    }
}
