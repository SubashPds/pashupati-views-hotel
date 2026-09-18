<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PackageImagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs(User::create(['name' => 'Admin', 'email' => 'images@example.test', 'password' => 'password', 'role' => 'superadmin', 'is_active' => true]));
    }

    private function photo(string $name = 'photo.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
    }

    public function test_cover_and_gallery_upload_append_and_display(): void
    {
        $this->get(route('admin.packages.create'))->assertOk()->assertSee('gallery_images[]', false)->assertSee('data-room-image-upload', false);
        $this->post(route('admin.packages.store'), [
            'name' => 'Temple Stay', 'cover_image' => $this->photo('cover.png'),
            'gallery_images' => [$this->photo('one.png'), $this->photo('two.png')],
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.packages.index'));
        $package = Package::firstOrFail();
        $originalCover = $package->cover_image;
        Storage::disk('public')->assertExists($originalCover);
        $this->assertCount(2, $package->images);
        foreach ($package->images as $image) Storage::disk('public')->assertExists($image->image_path);
        $this->get(route('admin.packages.edit', $package))->assertOk()->assertSee('Remove gallery image 1');
        $this->get('/')->assertOk()->assertSee('data-package-thumbnail', false)->assertSee(Storage::url($package->images->first()->image_path));

        $this->put(route('admin.packages.update', $package), ['name' => 'Temple Stay'])->assertSessionHasNoErrors();
        $this->assertSame($originalCover, $package->fresh()->cover_image);
        $this->assertSame(2, $package->images()->count());
        $this->put(route('admin.packages.update', $package), [
            'name' => 'Temple Stay', 'cover_image' => $this->photo('replacement.png'), 'gallery_images' => [$this->photo('three.png')],
        ])->assertSessionHasNoErrors();
        $this->assertSame([1, 2, 3], $package->images()->pluck('sort_order')->all());
        Storage::disk('public')->assertMissing($originalCover);
        Storage::disk('public')->assertExists($package->fresh()->cover_image);
    }

    public function test_invalid_gallery_rejects_all_changes_before_uploading(): void
    {
        $invalid = fn () => UploadedFile::fake()->createWithContent('bad.txt', 'Not an image');
        $this->post(route('admin.packages.store'), [
            'name' => 'Invalid', 'cover_image' => $this->photo(), 'gallery_images' => [$this->photo(), $invalid()],
        ])->assertSessionHasErrors('gallery_images.1');
        $this->assertDatabaseCount('packages', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
        $package = Package::create(['name' => 'Original']);
        foreach ([[$invalid()], [$this->photo()->size(4097)], 'invalid'] as $files) {
            $this->put(route('admin.packages.update', $package), [
                'name' => 'Changed', 'cover_image' => $this->photo(), 'gallery_images' => $files,
            ])->assertSessionHasErrors();
        }
        $this->assertSame('Original', $package->fresh()->name);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_deleting_an_image_or_package_cleans_up_files_and_rows(): void
    {
        $this->post(route('admin.packages.store'), [
            'name' => 'Delete Test', 'cover_image' => $this->photo(), 'gallery_images' => [$this->photo(), $this->photo()],
        ])->assertSessionHasNoErrors();
        $package = Package::firstOrFail();
        $image = $package->images()->first();
        $this->delete(route('admin.packages.images.destroy', $image))->assertRedirect();
        Storage::disk('public')->assertMissing($image->image_path);
        $this->assertSame(1, $package->images()->count());
        $this->delete(route('admin.packages.destroy', $package))->assertRedirect();
        $this->assertDatabaseCount('package_images', 0);
        $this->assertDatabaseCount('packages', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
