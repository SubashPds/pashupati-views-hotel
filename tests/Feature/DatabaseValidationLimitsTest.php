<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Package;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DatabaseValidationLimitsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs(User::create([
            'name' => 'Limits Admin', 'email' => 'limits@example.test', 'password' => 'password',
            'role' => 'superadmin', 'is_active' => true,
        ]));
    }

    public static function acceptedValues(): array
    {
        return [
            'FAQ maximum length' => ['faqs', 'question', str_repeat('q', 255), str_repeat('q', 255)],
            'FAQ multibyte maximum length' => ['faqs', 'question', str_repeat('界', 255), str_repeat('界', 255)],
            'package minimum guests limit' => ['packages', 'min_guests', 127, 127],
            'package maximum guests limit' => ['packages', 'max_guests', 127, 127],
            'package maximum price' => ['packages', 'price_from', '99999999.99', '99999999.99'],
            'room maximum price' => ['rooms', 'price_per_night', '99999999.99', '99999999.99'],
            'package zero price' => ['packages', 'price_from', '0', '0.00'],
            'room zero price' => ['rooms', 'price_per_night', 0, '0.00'],
            'package one decimal' => ['packages', 'price_from', '12.5', '12.50'],
            'room integer price' => ['rooms', 'price_per_night', 1200, '1200.00'],
        ];
    }

    #[DataProvider('acceptedValues')]
    public function test_supported_values_save_on_create_and_update(string $resource, string $field, mixed $value, mixed $expected): void
    {
        [$model, $data] = $this->form($resource);
        $input = array_replace($data, [$field => $value]);

        $this->post(route('admin.'.$resource.'.store'), $input)
            ->assertSessionHasNoErrors()->assertRedirect();
        $record = $model::firstOrFail();
        $this->assertSame($expected, $record->$field);

        $record->update([$field => match ($field) {
            'question' => 'Original question',
            'price_from', 'price_per_night' => 10,
            default => 1,
        }]);
        $this->put(route('admin.'.$resource.'.update', $record), $input)
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($expected, $record->fresh()->$field);
    }

    public static function rejectedValues(): array
    {
        return [
            'FAQ exceeds maximum' => ['faqs', 'question', str_repeat('q', 256)],
            'FAQ multibyte exceeds maximum' => ['faqs', 'question', str_repeat('界', 256)],
            'package minimum guests overflow' => ['packages', 'min_guests', 128],
            'package maximum guests overflow' => ['packages', 'max_guests', 128],
            'package price overflow' => ['packages', 'price_from', '100000000.00'],
            'room price overflow' => ['rooms', 'price_per_night', '100000000.00'],
            'package excessive decimal places' => ['packages', 'price_from', '12.345'],
            'room excessive decimal places' => ['rooms', 'price_per_night', '12.345'],
            'package rounding overflow' => ['packages', 'price_from', '99999999.999'],
            'room rounding overflow' => ['rooms', 'price_per_night', '99999999.999'],
            'package exponential overflow' => ['packages', 'price_from', '1e8'],
            'room exponential overflow' => ['rooms', 'price_per_night', '1e8'],
        ];
    }

    #[DataProvider('rejectedValues')]
    public function test_unsupported_values_are_rejected_before_database_or_media_changes(string $resource, string $field, mixed $value): void
    {
        [$model, $data] = $this->form($resource);
        $record = $model::create($data);
        $original = $record->refresh()->getAttributes();

        foreach (['store', 'update'] as $action) {
            $input = array_replace($data, [$field => $value]);
            if ($resource !== 'faqs') {
                $input['cover_image'] = $this->photo();
                $input['gallery_images'] = [$this->photo()];
            }
            $response = $action === 'store'
                ? $this->post(route('admin.'.$resource.'.store'), $input)
                : $this->put(route('admin.'.$resource.'.update', $record), $input);

            $response->assertRedirect()->assertSessionHasErrors($field);
            $this->assertDatabaseCount($record->getTable(), 1);
            $this->assertSame($original, $record->fresh()->getAttributes());
            $this->assertSame([], Storage::disk('public')->allFiles());
        }
    }

    private function form(string $resource): array
    {
        return match ($resource) {
            'faqs' => [Faq::class, ['question' => 'Check-in time?', 'answer' => 'After noon.']],
            'packages' => [Package::class, ['name' => 'Test Package']],
            'rooms' => [Room::class, ['name' => 'Test Room', 'category' => 'deluxe', 'price_per_night' => 1000, 'max_guests' => 2]],
        };
    }

    private function photo(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('photo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
    }
}
