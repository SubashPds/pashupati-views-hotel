<?php

namespace Tests\Unit;

use App\Models\Package;
use App\Models\Room;
use App\Services\DisplayCurrency;
use App\Services\GalleryPreview;
use App\Services\ImagePreview;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CardImagePreviewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_room_and_package_covers_fall_back_without_processing_on_read(): void
    {
        $disk = Storage::disk('public');
        foreach ([Room::class => 'rooms', Package::class => 'packages'] as $model => $directory) {
            $source = $directory.'/cover.png';
            $disk->put($source, $this->png());
            $item = new $model(['cover_image' => $source]);

            $this->assertSame($item->cover_image_url, $item->cover_preview_url);
            $this->assertSame([$source], $disk->allFiles($directory));
            $this->assertSame('', (new $model)->cover_preview_url);
            $remote = new $model(['cover_image' => 'https://example.com/cover.jpg']);
            $this->assertSame($remote->cover_image_url, $remote->cover_preview_url);
        }
    }

    public function test_cover_previews_resize_both_types_and_keep_original_urls(): void
    {
        $disk = Storage::disk('public');
        $original = $this->png();
        foreach ([Room::class => 'rooms', Package::class => 'packages'] as $model => $directory) {
            $source = $directory.'/cover.png';
            $disk->put($source, $original);
            $preview = (new ImagePreview)->generate($source);
            if ($preview === null) {
                $this->markTestSkipped('Requires GD with WebP or ImageMagick.');
            }
            $size = getimagesize($disk->path($preview));
            $this->assertSame([800, 600, 'image/webp'], [$size[0], $size[1], $size['mime']]);
            $this->assertSame($original, $disk->get($source));
            $item = new $model(['cover_image' => $source]);
            $this->assertSame($disk->url($preview), $item->cover_preview_url);
            $this->assertSame($disk->url($source), $item->cover_image_url);
        }
    }

    public function test_cards_render_previews_while_details_render_originals_without_database_queries(): void
    {
        $disk = Storage::disk('public');
        $currency = new DisplayCurrency('NPR', ['NPR' => 1]);
        foreach ([Room::class => ['room', 'rooms'], Package::class => ['package', 'packages']] as $model => [$kind, $directory]) {
            $source = $directory.'/cover.png';
            $preview = (new ImagePreview)->path($source);
            $disk->put($preview, 'cached preview');
            $item = new $model(['name' => 'Sample stay', 'cover_image' => $source]);
            $item->id = 1;
            if ($kind === 'room') {
                $item->category = 'deluxe';
                $item->price_per_night = 5000;
            }
            $item->setRelation('images', collect());

            $cards = view('frontend.sections.'.$directory, [$directory => collect([$item]), 'settings' => [], 'currency' => $currency])->render();
            $details = view('frontend.partials.'.$kind.'-details', [$kind === 'room' ? 'room' : 'pkg' => $item, 'currency' => $currency])->render();

            $this->assertStringContainsString('data-card-preview-src="'.$disk->url($preview).'"', $cards);
            $this->assertStringNotContainsString($disk->url($source), $cards);
            $this->assertStringContainsString('src="'.$disk->url($source).'"', $details);
            $this->assertStringNotContainsString($disk->url($preview), $details);
        }
    }

    public function test_deleting_one_cached_cover_preserves_other_previews_and_originals(): void
    {
        $previews = new ImagePreview;
        $disk = Storage::disk('public');
        foreach (['rooms/old.png', 'rooms/new.png', 'packages/cover.png'] as $source) {
            $disk->put($source, 'original');
            $disk->put($previews->path($source), 'preview');
        }
        $previews->delete('rooms/old.png');

        $this->assertFalse($disk->exists($previews->path('rooms/old.png')));
        $this->assertTrue($disk->exists('rooms/old.png'));
        $this->assertTrue($disk->exists($previews->path('rooms/new.png')));
        $this->assertTrue($disk->exists($previews->path('packages/cover.png')));
    }

    public function test_detail_galleries_are_excluded_and_gallery_preview_paths_remain_compatible(): void
    {
        $previews = new ImagePreview;
        foreach (['rooms/gallery/photo.jpg', 'packages/gallery/photo.jpg', 'rooms/previews/photo.webp', 'packages/previews/photo.webp', 'rooms/../cover.jpg', 'https://example.com/rooms/cover.jpg'] as $source) {
            $this->assertNull($previews->path($source));
        }
        $gallery = new GalleryPreview;
        $this->assertNull($gallery->path('rooms/cover.jpg'));
        $this->assertSame('gallery/previews/'.hash('sha256', 'gallery/photo.jpg').'-800.webp', $gallery->path('gallery/photo.jpg'));
        $this->assertSame($gallery->path('gallery/photo.jpg'), $previews->path('gallery/photo.jpg'));
    }

    public function test_backfill_scans_only_cover_uploads_without_a_database(): void
    {
        $disk = Storage::disk('public');
        foreach (['rooms/broken.jpg', 'packages/broken.jpg', 'rooms/gallery/detail.jpg', 'packages/gallery/detail.jpg', 'gallery/photo.jpg'] as $source) {
            $disk->put($source, 'invalid image');
        }

        $this->artisan('cards:previews')
            ->expectsOutput('Room/package cover previews ready: 0; skipped: 2.')
            ->assertSuccessful();
    }

    private function png(): string
    {
        $chunk = fn (string $type, string $data) => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
        $pixels = str_repeat("\0".str_repeat("\xD4\xAF\x5B", 1200), 900);

        return "\x89PNG\r\n\x1A\n"
            .$chunk('IHDR', pack('NNCCCCC', 1200, 900, 8, 2, 0, 0, 0))
            .$chunk('IDAT', gzcompress($pixels))
            .$chunk('IEND', '');
    }
}
