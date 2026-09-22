<?php

namespace Tests\Unit;

use App\Models\GalleryItem;
use App\Services\GalleryPreview;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryPreviewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_preview_urls_fall_back_without_processing_on_read(): void
    {
        $disk = Storage::disk('public');
        $disk->put('gallery/photo.png', $this->png(1200, 900));
        $item = new GalleryItem(['image_path' => 'gallery/photo.png']);

        $this->assertSame($item->image_url, $item->preview_url);
        $this->assertSame(['gallery/photo.png'], $disk->allFiles('gallery'));
        $this->assertSame('https://example.com/photo.jpg', (new GalleryItem(['image_path' => 'https://example.com/photo.jpg']))->preview_url);
    }

    public function test_generation_resizes_and_caches_without_changing_the_original(): void
    {
        $disk = Storage::disk('public');
        $original = $this->png(1600, 1200);
        $disk->put('gallery/photo.png', $original);
        $previews = new GalleryPreview;
        $path = $previews->generate('gallery/photo.png');
        if ($path === null) {
            $this->markTestSkipped('Requires GD with WebP or ImageMagick.');
        }

        $size = getimagesize($disk->path($path));
        $this->assertSame([800, 600, 'image/webp'], [$size[0], $size[1], $size['mime']]);
        $this->assertSame($original, $disk->get('gallery/photo.png'));
        $this->assertSame($disk->url($path), (new GalleryItem(['image_path' => 'gallery/photo.png']))->preview_url);

        // Reusing a preview must avoid conversion, even if the source is now unreadable.
        $disk->put('gallery/photo.png', 'invalid image');
        $this->assertSame($path, $previews->generate('gallery/photo.png'));
        $this->assertNull($previews->generate('gallery/photo.png', true));
        $this->assertTrue($disk->exists($path), 'A failed replacement keeps the previous valid preview.');
        $previews->delete('gallery/photo.png');
        $this->assertFalse($disk->exists($path));
        $this->assertTrue($disk->exists('gallery/photo.png'));
    }

    public function test_small_images_are_not_upscaled(): void
    {
        $disk = Storage::disk('public');
        $disk->put('gallery/small.png', $this->png(40, 30));
        $path = (new GalleryPreview)->generate('gallery/small.png');
        if ($path === null) {
            $this->markTestSkipped('Requires GD with WebP or ImageMagick.');
        }

        $size = getimagesize($disk->path($path));
        $this->assertSame([40, 30], [$size[0], $size[1]]);
    }

    public function test_remote_video_missing_and_unsafe_paths_are_skipped(): void
    {
        $previews = new GalleryPreview;
        foreach (['https://example.com/photo.jpg', '/storage/gallery/photo.jpg', 'gallery/video.mp4', 'gallery/../photo.jpg', 'gallery/previews/existing.webp', 'gallery/missing.jpg'] as $source) {
            $this->assertNull($previews->generate($source));
        }
        Storage::disk('public')->put('gallery/broken.jpg', 'not an image');
        $this->assertNull($previews->generate('gallery/broken.jpg'));
        $this->assertSame([], Storage::disk('public')->files('gallery/previews'));
    }

    public function test_backfill_scans_uploads_without_a_database(): void
    {
        $disk = Storage::disk('public');
        $disk->put('gallery/video.mp4', 'video');
        $disk->put('gallery/broken.jpg', 'invalid image');

        $this->artisan('gallery:previews')
            ->expectsOutput('Gallery previews ready: 0; skipped: 1.')
            ->assertSuccessful();
    }

    private function png(int $width, int $height): string
    {
        $chunk = fn (string $type, string $data) => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
        $pixels = str_repeat("\0".str_repeat("\xD4\xAF\x5B", $width), $height);

        return "\x89PNG\r\n\x1A\n"
            .$chunk('IHDR', pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0))
            .$chunk('IDAT', gzcompress($pixels))
            .$chunk('IEND', '');
    }
}
