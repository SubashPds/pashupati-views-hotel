<?php

namespace App\Services;

use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class MediaFiles
{
    private array $uploaded = [];

    private array $obsolete = [];

    /** Keep filesystem cleanup outside the database operation's failure path. */
    public static function transaction(Closure $callback): mixed
    {
        $files = new self;
        $committed = false;

        try {
            $result = DB::transaction(function () use ($callback, $files, &$committed) {
                DB::afterCommit(function () use (&$committed) {
                    $committed = true;
                });

                return $callback($files);
            });
        } catch (Throwable $exception) {
            if (! $committed) {
                $files->delete($files->uploaded);
            }

            throw $exception;
        }

        // If an enclosing transaction is still open, its outcome owns cleanup.
        if (DB::transactionLevel() > 0) {
            DB::afterRollBack(fn () => $files->delete($files->uploaded));
        }
        DB::afterCommit(fn () => $files->delete($files->obsolete));

        return $result;
    }

    public function store(UploadedFile $file, string $directory, bool $preview = false): string
    {
        // Remember the intended path even when a write fails partway through.
        $path = $file->hashName($directory);
        $this->uploaded[] = $path;
        $stored = $file->store($directory, 'public');
        if ($stored !== $path || ! Storage::disk('public')->exists($path)) {
            throw new RuntimeException('The uploaded media could not be stored.');
        }

        if ($preview) {
            app(ImagePreview::class)->generate($path);
        }

        return $path;
    }

    public function deleteAfterCommit(?string $path): void
    {
        if ($path) {
            $this->obsolete[] = $path;
        }
    }

    private function delete(array $paths): void
    {
        $previews = app(ImagePreview::class);
        foreach (array_unique($paths) as $path) {
            foreach (array_filter([$previews->path($path), $path]) as $file) {
                try {
                    if (! Storage::disk('public')->delete($file)) {
                        Log::warning('Media file cleanup failed.', ['path' => $file]);
                    }
                } catch (Throwable $exception) {
                    // A cleanup failure must not discard a committed replacement
                    // or hide the original persistence exception.
                    report($exception);
                }
            }
        }
    }
}
