<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Package;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OptionalAdminFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs(User::create([
            'name' => 'Forms Admin', 'email' => 'forms@example.test', 'password' => 'password',
            'role' => 'superadmin', 'is_active' => true,
        ]));
    }

    public static function forms(): array
    {
        return [
            'service' => [
                Service::class, 'services', ['title' => 'Airport pickup'],
                ['price_label' => 'Price on request', 'sort_order' => 0],
                ['price_label' => 'NPR 500', 'sort_order' => 4], ['description', 'icon'],
            ],
            'package' => [
                Package::class, 'packages', ['name' => 'Weekend Stay'],
                ['min_guests' => 1, 'sort_order' => 0],
                ['min_guests' => 5, 'sort_order' => 4], ['max_guests', 'price_label', 'price_from'],
            ],
            'faq' => [
                Faq::class, 'faqs', ['question' => 'When is check-in?', 'answer' => 'After noon.'],
                ['sort_order' => 0], ['sort_order' => 4], [],
            ],
            'gallery' => [
                GalleryItem::class, 'gallery', [],
                ['section' => 'general'], ['section' => 'dining'], ['title', 'badge_label'],
            ],
        ];
    }

    #[DataProvider('forms')]
    public function test_cleared_fields_use_defaults_on_create(string $model, string $resource, array $data, array $defaults, array $custom, array $nullable): void
    {
        foreach (['', '   ', null] as $index => $blank) {
            $input = $data;
            if ($resource === 'packages') {
                $input['name'] .= ' '.$index;
            }
            $input = array_replace($input, array_fill_keys([...array_keys($defaults), ...$nullable], $blank));

            $this->post(route('admin.'.$resource.'.store'), $this->withUpload($resource, $input))
                ->assertSessionHasNoErrors()->assertRedirect();

            $record = $model::latest('id')->firstOrFail();
            $this->assertSame($defaults, $record->only(array_keys($defaults)));
            foreach ($nullable as $field) {
                $this->assertNull($record->$field, $field);
            }
        }
    }

    #[DataProvider('forms')]
    public function test_cleared_fields_use_defaults_on_update(string $model, string $resource, array $data, array $defaults, array $custom, array $nullable): void
    {
        $record = $this->record($model, $data + $custom);

        foreach (['', '   ', null] as $blank) {
            $record->update($custom);
            $input = array_replace($data, array_fill_keys([...array_keys($defaults), ...$nullable], $blank));

            $this->put(route('admin.'.$resource.'.update', $record), $input)
                ->assertSessionHasNoErrors()->assertRedirect();

            $record->refresh();
            $this->assertSame($defaults, $record->only(array_keys($defaults)));
            foreach ($nullable as $field) {
                $this->assertNull($record->$field, $field);
            }
            $this->assertFalse($record->is_active);
        }
        if ($record instanceof GalleryItem) {
            $this->assertSame('gallery/original.png', $record->image_path);
            $this->assertSame('Original image', Storage::disk('public')->get($record->image_path));
        }
    }

    #[DataProvider('forms')]
    public function test_omitted_fields_use_database_defaults_on_create(string $model, string $resource, array $data, array $defaults): void
    {
        $this->post(route('admin.'.$resource.'.store'), $this->withUpload($resource, $data))
            ->assertSessionHasNoErrors()->assertRedirect();

        $record = $model::firstOrFail();
        $this->assertSame($defaults, $record->only(array_keys($defaults)));
    }

    #[DataProvider('forms')]
    public function test_omitted_fields_preserve_existing_values_on_update(string $model, string $resource, array $data, array $defaults, array $custom): void
    {
        $record = $this->record($model, $data + $custom);

        $this->put(route('admin.'.$resource.'.update', $record), $data)
            ->assertSessionHasNoErrors()->assertRedirect();

        $record->refresh();
        $this->assertSame($custom, $record->only(array_keys($custom)));
        $this->assertFalse($record->is_active);
    }

    #[DataProvider('forms')]
    public function test_explicit_values_are_saved_including_zero(string $model, string $resource, array $data, array $defaults, array $custom): void
    {
        $record = $this->record($model, $data + $defaults);
        $this->put(route('admin.'.$resource.'.update', $record), $data + $custom)
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($custom, $record->fresh()->only(array_keys($custom)));

        $zeroFields = match ($resource) {
            'services' => ['price_label' => '0', 'sort_order' => 0],
            'packages', 'faqs' => ['sort_order' => 0],
            'gallery' => ['section' => '0'],
        };
        $this->put(route('admin.'.$resource.'.update', $record), $data + $zeroFields)
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($zeroFields, $record->fresh()->only(array_keys($zeroFields)));
    }

    #[DataProvider('forms')]
    public function test_invalid_nonblank_values_are_rejected_without_changes(string $model, string $resource, array $data, array $defaults, array $custom): void
    {
        $record = $this->record($model, $data + $custom);
        $original = $record->refresh()->getAttributes();
        $invalid = match ($resource) {
            'services' => ['price_label' => ['invalid'], 'sort_order' => 'invalid'],
            'packages' => ['min_guests' => 0, 'sort_order' => 'invalid'],
            'faqs' => ['sort_order' => 'invalid'],
            'gallery' => ['section' => ['invalid']],
        };

        $this->post(route('admin.'.$resource.'.store'), $this->withUpload($resource, $data + $invalid))
            ->assertSessionHasErrors(array_keys($invalid));
        $this->put(route('admin.'.$resource.'.update', $record), $data + $invalid)
            ->assertSessionHasErrors(array_keys($invalid));

        $this->assertDatabaseCount($record->getTable(), 1);
        $this->assertSame($original, $record->fresh()->getAttributes());
    }

    private function record(string $model, array $data): Model
    {
        if ($model === GalleryItem::class) {
            $data['image_path'] = 'gallery/original.png';
            Storage::disk('public')->put($data['image_path'], 'Original image');
        }

        return $model::create($data + ['is_active' => false]);
    }

    private function withUpload(string $resource, array $data): array
    {
        if ($resource === 'gallery') {
            $data['images'] = [UploadedFile::fake()->createWithContent('photo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='))];
        }

        return $data;
    }
}
