<?php

namespace Tests\Feature;

use App\Http\Middleware\AdminAccess;
use App\Models\Blog;
use App\Models\GalleryItem;
use App\Models\HeroSlide;
use App\Models\Package;
use App\Models\Promotion;
use App\Models\Restaurant;
use App\Models\Room;
use App\Models\User;
use App\Services\ImagePreview;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class MediaPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs(User::create([
            'name' => 'Media Admin', 'email' => 'media@example.test', 'password' => 'password',
            'role' => 'superadmin', 'is_active' => true,
        ]));

        // Make preview cleanup deterministic, without requiring GD or ImageMagick.
        $previews = new ImagePreview;
        $this->partialMock(ImagePreview::class, function ($mock) use ($previews) {
            $mock->shouldReceive('generate')->andReturnUsing(function (string $source) use ($previews) {
                $path = $previews->path($source);
                if ($path) {
                    Storage::disk('public')->put($path, 'Generated preview');
                }

                return $path;
            });
        });
    }

    public static function replacements(): array
    {
        return [
            'room' => [Room::class, 'rooms', 'cover_image', 'cover_image', 'rooms', ['name' => 'Original room', 'category' => 'deluxe', 'price_per_night' => 1000, 'max_guests' => 2]],
            'package' => [Package::class, 'packages', 'cover_image', 'cover_image', 'packages', ['name' => 'Original package']],
            'blog' => [Blog::class, 'blogs', 'cover_image', 'cover_image', 'blogs', ['title' => 'Original blog', 'slug' => 'original-blog', 'content' => 'Content', 'is_published' => false]],
            'hero slide' => [HeroSlide::class, 'hero-slides', 'media_path', 'media', 'hero-slides', ['title' => 'Original slide', 'media_type' => 'image', 'sort_order' => 0, 'is_active' => true]],
            'promotion' => [Promotion::class, 'promotions', 'image_path', 'offer_image', 'offers', ['title' => 'Original promotion', 'sort_order' => 0, 'is_active' => true]],
            'restaurant' => [Restaurant::class, 'restaurant', 'menu_image', 'menu_image', 'restaurant/menu', ['name' => 'Original restaurant', 'is_active' => true]],
        ];
    }

    public static function stays(): array
    {
        return array_intersect_key(self::replacements(), array_flip(['room', 'package']));
    }

    public static function deletions(): array
    {
        $cases = self::replacements();
        unset($cases['restaurant']);
        $cases['gallery'] = [GalleryItem::class, 'gallery', 'image_path', 'images', 'gallery', ['title' => 'Original gallery item']];

        return $cases;
    }

    #[DataProvider('replacements')]
    public function test_failed_replacement_preserves_original_and_cleans_new_files(string $model, string $resource, string $column, string $field, string $directory, array $data): void
    {
        $record = $this->original($model, $column, $directory, $data);
        $before = Storage::disk('public')->allFiles();
        Event::listen('eloquent.updated: '.$model, fn () => throw new RuntimeException('Simulated database failure'));

        $this->put($this->updateUrl($resource, $record), $data + [$field => $this->photo()])->assertStatus(500);

        $this->assertSame($record->$column, $record->fresh()->$column);
        $this->assertSame($before, Storage::disk('public')->allFiles());
        $this->assertSame('Original image', Storage::disk('public')->get($record->$column));
    }

    #[DataProvider('replacements')]
    public function test_storage_returning_false_does_not_replace_original(string $model, string $resource, string $column, string $field, string $directory, array $data): void
    {
        $record = $this->original($model, $column, $directory, $data);
        $disk = Storage::disk('public');
        $before = $disk->allFiles();
        $broken = Mockery::mock($disk);
        $broken->shouldReceive('putFileAs')->once()->andReturnUsing(function ($directory, $file, $name) use ($disk) {
            $disk->put($directory.'/'.$name, 'Partial upload');

            return false;
        });
        Storage::set('public', $broken);

        $this->put($this->updateUrl($resource, $record), $data + [$field => $this->photo()])->assertStatus(500);

        $this->assertSame($record->$column, $record->fresh()->$column);
        $this->assertSame($before, $disk->allFiles());
    }

    #[DataProvider('replacements')]
    public function test_successful_replacement_removes_old_files_only_after_commit(string $model, string $resource, string $column, string $field, string $directory, array $data): void
    {
        $record = $this->original($model, $column, $directory, $data);
        $oldFiles = Storage::disk('public')->allFiles();
        DB::beginTransaction();

        $this->put($this->updateUrl($resource, $record), $data + [$field => $this->photo()])->assertSessionHasNoErrors()->assertRedirect();
        $newPath = $record->fresh()->$column;
        $this->assertNotSame($record->$column, $newPath);
        Storage::disk('public')->assertExists([...$oldFiles, $newPath]);

        DB::commit();

        Storage::disk('public')->assertMissing($oldFiles);
        Storage::disk('public')->assertExists($newPath);
        if ($preview = app(ImagePreview::class)->path($newPath)) {
            Storage::disk('public')->assertExists($preview);
        }
    }

    #[DataProvider('replacements')]
    public function test_outer_rollback_preserves_original_and_discards_replacement(string $model, string $resource, string $column, string $field, string $directory, array $data): void
    {
        $record = $this->original($model, $column, $directory, $data);
        $before = Storage::disk('public')->allFiles();
        DB::beginTransaction();
        $this->put($this->updateUrl($resource, $record), $data + [$field => $this->photo()])->assertRedirect();

        DB::rollBack();

        $this->assertSame($record->$column, $record->fresh()->$column);
        $this->assertSame($before, Storage::disk('public')->allFiles());
    }

    #[DataProvider('stays')]
    public function test_gallery_database_failure_rolls_back_cover_and_all_gallery_uploads(string $model, string $resource, string $column, string $field, string $directory, array $data): void
    {
        $record = $this->original($model, $column, $directory, $data);
        $existing = $record->images()->create(['image_path' => $directory.'/gallery/original.png', 'sort_order' => 1]);
        Storage::disk('public')->put($existing->image_path, 'Original gallery image');
        $before = Storage::disk('public')->allFiles();
        $count = 0;
        Event::listen('eloquent.created: '.get_class($existing), function () use (&$count) {
            if (++$count === 2) {
                throw new RuntimeException('Second gallery row failed');
            }
        });

        $this->put($this->updateUrl($resource, $record), $data + [
            $field => $this->photo(), 'gallery_images' => [$this->photo(), $this->photo()],
        ])->assertStatus(500);

        $this->assertSame($record->$column, $record->fresh()->$column);
        $this->assertSame([$existing->id], $record->images()->pluck('id')->all());
        $this->assertSame($before, Storage::disk('public')->allFiles());
    }

    #[DataProvider('stays')]
    public function test_failed_create_cleans_cover_previews_and_prior_gallery_uploads(string $model, string $resource, string $column, string $field, string $directory, array $data): void
    {
        $related = (new $model)->images()->getRelated();
        Event::listen('eloquent.created: '.get_class($related), fn () => throw new RuntimeException('Gallery save failed'));

        $this->post(route('admin.'.$resource.'.store'), $data + [
            $field => $this->photo(), 'gallery_images' => [$this->photo(), $this->photo()],
        ])->assertStatus(500);

        $this->assertDatabaseCount((new $model)->getTable(), 0);
        $this->assertDatabaseCount($related->getTable(), 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    #[DataProvider('stays')]
    public function test_gallery_storage_failure_rolls_back_cover_and_prior_uploads(string $model, string $resource, string $column, string $field, string $directory, array $data): void
    {
        $record = $this->original($model, $column, $directory, $data);
        $disk = Storage::disk('public');
        $before = $disk->allFiles();
        $broken = Mockery::mock($disk);
        $writes = 0;
        $broken->shouldReceive('putFileAs')->andReturnUsing(function (...$arguments) use ($disk, &$writes) {
            return ++$writes === 3 ? false : $disk->putFileAs(...$arguments);
        });
        Storage::set('public', $broken);

        $this->put($this->updateUrl($resource, $record), $data + [
            $field => $this->photo(), 'gallery_images' => [$this->photo(), $this->photo()],
        ])->assertStatus(500);

        $this->assertSame($record->$column, $record->fresh()->$column);
        $this->assertSame(0, $record->images()->count());
        $this->assertSame($before, $disk->allFiles());
    }

    public function test_gallery_batch_failure_removes_all_new_rows_and_previews(): void
    {
        $count = 0;
        Event::listen('eloquent.created: '.GalleryItem::class, function () use (&$count) {
            if (++$count === 2) {
                throw new RuntimeException('Second gallery item failed');
            }
        });

        $this->post(route('admin.gallery.store'), ['images' => [$this->photo(), $this->photo()]])->assertStatus(500);

        $this->assertDatabaseCount('gallery_items', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    #[DataProvider('deletions')]
    public function test_failed_delete_preserves_rows_and_files(string $model, string $resource, string $column, string $field, string $directory, array $data): void
    {
        $record = $this->original($model, $column, $directory, $data);
        if (in_array($resource, ['rooms', 'packages'], true)) {
            $image = $record->images()->create(['image_path' => $directory.'/gallery/original.png']);
            Storage::disk('public')->put($image->image_path, 'Original gallery image');
        }
        $before = Storage::disk('public')->allFiles();
        Event::listen('eloquent.deleted: '.$model, fn () => throw new RuntimeException('Delete failed'));

        $this->delete(route('admin.'.$resource.'.destroy', $record))->assertStatus(500);

        $this->assertModelExists($record);
        if (isset($image)) {
            $this->assertModelExists($image);
        }
        $this->assertSame($before, Storage::disk('public')->allFiles());
    }

    #[DataProvider('deletions')]
    public function test_successful_delete_removes_files_after_commit(string $model, string $resource, string $column, string $field, string $directory, array $data): void
    {
        $record = $this->original($model, $column, $directory, $data);
        if (in_array($resource, ['rooms', 'packages'], true)) {
            $image = $record->images()->create(['image_path' => $directory.'/gallery/original.png']);
            Storage::disk('public')->put($image->image_path, 'Original gallery image');
        }
        $before = Storage::disk('public')->allFiles();
        DB::beginTransaction();

        $this->delete(route('admin.'.$resource.'.destroy', $record))->assertRedirect();
        $this->assertModelMissing($record);
        $this->assertSame($before, Storage::disk('public')->allFiles());

        DB::commit();

        $this->assertSame([], Storage::disk('public')->allFiles());
        if (isset($image)) {
            $this->assertModelMissing($image);
        }
    }

    #[DataProvider('stays')]
    public function test_individual_gallery_delete_preserves_file_on_failure(string $model, string $resource, string $column, string $field, string $directory, array $data): void
    {
        $record = $this->original($model, $column, $directory, $data);
        $image = $record->images()->create(['image_path' => $directory.'/gallery/original.png']);
        Storage::disk('public')->put($image->image_path, 'Original gallery image');
        Event::listen('eloquent.deleted: '.get_class($image), fn () => throw new RuntimeException('Delete failed'));

        $this->delete(route('admin.'.$resource.'.images.destroy', $image))->assertStatus(500);

        $this->assertModelExists($image);
        Storage::disk('public')->assertExists($image->image_path);
    }

    public function test_cleanup_failure_keeps_the_committed_replacement(): void
    {
        $data = ['name' => 'Package'];
        $record = $this->original(Package::class, 'cover_image', 'packages', $data);
        $disk = Storage::disk('public');
        $broken = Mockery::mock($disk);
        $broken->shouldReceive('delete')->with(app(ImagePreview::class)->path($record->cover_image))->once()->andThrow(new RuntimeException('Preview deletion failed'));
        $broken->shouldReceive('delete')->with($record->cover_image)->once()->andReturn(false);
        Log::spy();
        Storage::set('public', $broken);

        $this->put(route('admin.packages.update', $record), $data + ['cover_image' => $this->photo()])->assertRedirect();

        $replacement = $record->fresh()->cover_image;
        $this->assertNotSame($record->cover_image, $replacement);
        $disk->assertExists([$replacement, app(ImagePreview::class)->path($replacement)]);
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_missing_uploaded_file_is_rejected_before_persistence(): void
    {
        $data = ['name' => 'Package'];
        $record = $this->original(Package::class, 'cover_image', 'packages', $data);
        $disk = Storage::disk('public');
        $before = $disk->allFiles();
        $broken = Mockery::mock($disk);
        $broken->shouldReceive('putFileAs')->once()->andReturnUsing(fn ($directory, $file, $name) => $directory.'/'.$name);
        Storage::set('public', $broken);

        $this->put(route('admin.packages.update', $record), $data + ['cover_image' => $this->photo()])->assertStatus(500);

        $this->assertSame($record->cover_image, $record->fresh()->cover_image);
        $this->assertSame($before, $disk->allFiles());
    }

    public function test_thrown_storage_error_preserves_original(): void
    {
        $data = ['name' => 'Package'];
        $record = $this->original(Package::class, 'cover_image', 'packages', $data);
        $disk = Storage::disk('public');
        $before = $disk->allFiles();
        $broken = Mockery::mock($disk);
        $broken->shouldReceive('putFileAs')->once()->andThrow(new RuntimeException('Disk unavailable'));
        Storage::set('public', $broken);

        $this->put(route('admin.packages.update', $record), $data + ['cover_image' => $this->photo()])->assertStatus(500);

        $this->assertSame($record->cover_image, $record->fresh()->cover_image);
        $this->assertSame($before, $disk->allFiles());
    }

    public function test_cancelled_save_preserves_original_and_cleans_upload(): void
    {
        $data = ['name' => 'Package'];
        $record = $this->original(Package::class, 'cover_image', 'packages', $data);
        $before = Storage::disk('public')->allFiles();
        Event::listen('eloquent.updating: '.Package::class, fn () => false);

        $this->put(route('admin.packages.update', $record), $data + ['cover_image' => $this->photo()])->assertStatus(500);

        $this->assertSame($record->cover_image, $record->fresh()->cover_image);
        $this->assertSame($before, Storage::disk('public')->allFiles());
    }

    private function original(string $model, string $column, string $directory, array $data): Model
    {
        $path = $directory.'/original.png';
        Storage::disk('public')->put($path, 'Original image');
        if ($preview = app(ImagePreview::class)->path($path)) {
            Storage::disk('public')->put($preview, 'Original preview');
        }

        return $model::create($data + [$column => $path]);
    }

    private function updateUrl(string $resource, Model $record): string
    {
        if ($resource === 'restaurant') {
            // Finding 4 independently blocks restaurant routes; test media handling here.
            $this->withoutMiddleware(AdminAccess::class);

            return route('admin.restaurant.update');
        }

        return route('admin.'.$resource.'.update', $record);
    }

    private function photo(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('photo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
    }
}
