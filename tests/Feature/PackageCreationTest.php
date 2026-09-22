<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use App\Services\ImagePreview;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PackageCreationTest extends TestCase
{
    use RefreshDatabase;

    private array $generatedPreviews = [];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs(User::create([
            'name' => 'Package Admin', 'email' => 'packages@example.test', 'password' => 'password',
            'role' => 'superadmin', 'is_active' => true,
        ]));

        // Exercise preview cleanup without depending on an image-processing extension.
        $previews = new ImagePreview;
        $this->partialMock(ImagePreview::class, function ($mock) use ($previews) {
            $mock->shouldReceive('generate')->andReturnUsing(function (string $source) use ($previews) {
                $path = $previews->path($source);
                Storage::disk('public')->put($path, 'Generated preview');
                $this->generatedPreviews[] = $path;

                return $path;
            });
        });
    }

    public static function duplicateNames(): array
    {
        return [
            'same name' => ['Weekend Stay', true],
            'punctuation' => ['Weekend Stay!', true],
            'case' => ['WEEKEND STAY', true],
            'separator' => ['Weekend-Stay', true],
            'whitespace' => ['  Weekend   Stay  ', true],
            'inactive package' => ['Weekend Stay!', false],
        ];
    }

    #[DataProvider('duplicateNames')]
    public function test_duplicate_slug_is_rejected_before_uploading(string $name, bool $active): void
    {
        $existing = Package::create(['name' => 'Weekend Stay', 'is_active' => $active]);
        $original = $existing->refresh()->getAttributes();
        $disk = Storage::disk('public');
        $guard = Mockery::mock($disk);
        $guard->shouldNotReceive('putFileAs');
        Storage::set('public', $guard);

        $this->from(route('admin.packages.create'))->post(route('admin.packages.store'), [
            'name' => $name, 'cover_image' => $this->photo(), 'gallery_images' => [$this->photo()],
        ])->assertRedirect(route('admin.packages.create'))->assertSessionHasErrors('name');

        $this->assertDatabaseCount('packages', 1);
        $this->assertDatabaseCount('package_images', 0);
        $this->assertSame($original, $existing->fresh()->getAttributes());
        $this->assertSame([], $disk->allFiles());
        $this->assertSame([], $this->generatedPreviews);
    }

    public function test_json_duplicate_returns_name_validation_error(): void
    {
        Package::create(['name' => 'Weekend Stay']);

        $this->postJson(route('admin.packages.store'), ['name' => 'Weekend Stay!'])
            ->assertUnprocessable()->assertJsonValidationErrors('name');

        $this->assertDatabaseCount('packages', 1);
    }

    public static function responseFormats(): array
    {
        return ['browser' => [false], 'json' => [true]];
    }

    #[DataProvider('responseFormats')]
    public function test_collision_after_validation_returns_name_error_and_cleans_uploads(bool $json): void
    {
        $winnerCover = 'packages/winner.png';
        Storage::disk('public')->put($winnerCover, 'Winning request image');
        Storage::disk('public')->put(app(ImagePreview::class)->path($winnerCover), 'Winning request preview');
        $before = Storage::disk('public')->allFiles();
        $inserted = false;

        // Simulate a competing insert after the availability query returns its
        // result, but before this request begins its media/database transaction.
        DB::listen(function (QueryExecuted $query) use (&$inserted, $winnerCover) {
            if ($inserted || ! str_contains($query->sql, 'exists') || ! str_contains($query->sql, 'packages')) {
                return;
            }
            $inserted = true;
            DB::table('packages')->insert([
                'name' => 'Weekend Stay', 'slug' => 'weekend-stay', 'cover_image' => $winnerCover,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        });

        $response = $this->from(route('admin.packages.create'))->post(route('admin.packages.store'), [
            'name' => 'Weekend Stay!', 'cover_image' => $this->photo(), 'gallery_images' => [$this->photo()],
        ], $json ? ['Accept' => 'application/json'] : []);

        if ($json) {
            $response->assertUnprocessable()->assertJsonValidationErrors('name');
        } else {
            $response->assertRedirect(route('admin.packages.create'))->assertSessionHasErrors('name');
        }
        $this->assertTrue($inserted);
        $this->assertCount(1, $this->generatedPreviews);
        $this->assertDatabaseCount('packages', 1);
        $this->assertDatabaseCount('package_images', 0);
        $this->assertDatabaseHas('packages', ['name' => 'Weekend Stay', 'slug' => 'weekend-stay', 'cover_image' => $winnerCover]);
        $this->assertSame($before, Storage::disk('public')->allFiles());
    }

    public function test_distinct_package_keeps_its_generated_slug_and_media(): void
    {
        Package::create(['name' => 'Weekend Stay']);

        $this->post(route('admin.packages.store'), [
            'name' => 'Weekend Stay Deluxe!', 'slug' => 'weekend-stay',
            'cover_image' => $this->photo(), 'gallery_images' => [$this->photo(), $this->photo()],
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.packages.index'));

        $package = Package::where('slug', 'weekend-stay-deluxe')->firstOrFail();
        $this->assertDatabaseCount('packages', 2);
        $this->assertCount(2, $package->images);
        Storage::disk('public')->assertExists([
            $package->cover_image, app(ImagePreview::class)->path($package->cover_image),
            ...$package->images->pluck('image_path')->all(),
        ]);
    }

    public function test_other_unique_constraint_failures_are_not_reported_as_duplicate_names(): void
    {
        $existing = Package::create(['name' => 'Existing package']);
        Event::listen('eloquent.creating: '.Package::class, function (Package $package) use ($existing) {
            $package->id = $existing->id;
        });

        $this->postJson(route('admin.packages.store'), ['name' => 'Distinct package'])->assertStatus(500);

        $this->assertDatabaseCount('packages', 1);
        $this->assertSame('Existing package', $existing->fresh()->name);
    }

    public function test_existing_package_can_be_edited_without_changing_its_slug(): void
    {
        $package = Package::create(['name' => 'Weekend Stay']);

        foreach (['Weekend Stay', 'Renamed package'] as $name) {
            $this->put(route('admin.packages.update', $package), ['name' => $name])
                ->assertSessionHasNoErrors()->assertRedirect(route('admin.packages.index'));

            $this->assertSame($name, $package->fresh()->name);
            $this->assertSame('weekend-stay', $package->fresh()->slug);
        }
    }

    private function photo(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('photo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
    }
}
