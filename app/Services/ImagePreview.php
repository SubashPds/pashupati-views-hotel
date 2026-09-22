<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

class ImagePreview
{
    public function path(?string $source): ?string
    {
        $directory = explode('/', $source ?? '')[0];
        if (! $source || ! in_array($directory, ['gallery', 'rooms', 'packages'], true)
            || str_contains($source, '..') || str_contains($source, ':')
            || str_contains($source, '\\') || str_contains($source, "\0")
            || str_starts_with($source, 'gallery/previews/')
            || ($directory !== 'gallery' && dirname($source) !== $directory)
            || ! in_array(strtolower(pathinfo($source, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'avif'], true)) {
            return null;
        }

        return $directory.'/previews/'.hash('sha256', $source).'-800.webp';
    }

    /** Reading a preview URL never starts image processing. */
    public function url(?string $source): ?string
    {
        $path = $this->path($source);
        $disk = Storage::disk('public');

        return $path && $disk->exists($path) ? $disk->url($path) : null;
    }

    public function delete(?string $source): void
    {
        if ($path = $this->path($source)) {
            Storage::disk('public')->delete($path);
        }
    }

    /** Generate at upload time or through a backfill command, keeping the original intact. */
    public function generate(string $source, bool $force = false): ?string
    {
        $path = $this->path($source);
        if (! $path || config('filesystems.disks.public.driver') !== 'local') {
            return null;
        }

        $disk = Storage::disk('public');
        if (! $force && $disk->exists($path)) {
            return $path;
        }

        $temporary = null;
        try {
            $original = realpath($disk->path($source));
            $root = realpath($disk->path(''));
            if (! $original || ! $root || ! str_starts_with($original, $root.DIRECTORY_SEPARATOR)) {
                return null;
            }
            $size = @getimagesize($original);
            if (! $size || ! in_array($size['mime'], ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp', 'image/avif'], true)) {
                return null;
            }

            $disk->makeDirectory(dirname($path));
            $temporary = tempnam(dirname($disk->path($path)), '.preview-');
            if ($temporary === false) {
                return null;
            }

            if (! $this->generateWithGd($original, $temporary, $size)
                && ! $this->generateWithImageMagick($original, $temporary)) {
                return null;
            }

            $previewSize = @getimagesize($temporary);
            if (! $previewSize || $previewSize['mime'] !== 'image/webp' || max($previewSize[0], $previewSize[1]) > 800) {
                return null;
            }

            // Publish complete previews atomically; browsers never see partial files.
            if (! rename($temporary, $disk->path($path))) {
                return null;
            }
            $disk->setVisibility($path, 'public');

            return $path;
        } catch (Throwable $exception) {
            report($exception);

            return null;
        } finally {
            if ($temporary && is_file($temporary)) {
                unlink($temporary);
            }
        }
    }

    private function generateWithGd(string $source, string $destination, array $size): bool
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return false;
        }
        // Large camera images can exceed PHP's memory limit before GD can fail safely.
        $memoryLimit = ini_parse_quantity(ini_get('memory_limit'));
        $memoryNeeded = $size[0] * $size[1] * 8 + 16 * 1024 * 1024;
        if ($memoryLimit > 0 && memory_get_usage(true) + $memoryNeeded > $memoryLimit) {
            return false;
        }

        $image = @imagecreatefromstring(file_get_contents($source));
        if (! $image) {
            return false;
        }
        $thumbnail = null;
        try {
            $ratio = min(1, 800 / max($size[0], $size[1]));
            $width = max(1, (int) round($size[0] * $ratio));
            $height = max(1, (int) round($size[1] * $ratio));
            $thumbnail = imagecreatetruecolor($width, $height);
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);

            $orientation = $size['mime'] === 'image/jpeg' && function_exists('exif_read_data')
                ? (int) ((@exif_read_data($source))['Orientation'] ?? 1) : 1;
            if (in_array($orientation, [2, 5, 7], true)) {
                imageflip($thumbnail, IMG_FLIP_HORIZONTAL);
            } elseif ($orientation === 4) {
                imageflip($thumbnail, IMG_FLIP_VERTICAL);
            }
            $rotation = match ($orientation) {
                3 => 180,
                5, 8 => 90,
                6, 7 => -90,
                default => 0,
            };
            if ($rotation) {
                $rotated = imagerotate($thumbnail, $rotation, 0);
                imagedestroy($thumbnail);
                $thumbnail = $rotated;
            }

            return imagewebp($thumbnail, $destination, 76);
        } finally {
            imagedestroy($image);
            if ($thumbnail) {
                imagedestroy($thumbnail);
            }
        }
    }

    private function generateWithImageMagick(string $source, string $destination): bool
    {
        $finder = new ExecutableFinder;
        $binary = $finder->find('magick') ?? $finder->find('convert');
        if (! $binary) {
            return false;
        }

        $process = new Process([
            $binary, '-limit', 'thread', '1', '-limit', 'memory', '128MiB', '-limit', 'map', '256MiB',
            $source.'[0]', '-auto-orient', '-thumbnail', '800x800>', '-strip', '-quality', '76', 'webp:'.$destination,
        ]);
        $process->setTimeout(30);
        $process->run();

        return $process->isSuccessful();
    }
}
