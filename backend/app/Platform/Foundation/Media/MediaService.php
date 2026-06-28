<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Media;

use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Foundation\Media\Models\Media;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The single upload mechanism for the whole system. Validates, stores, checksums
 * and registers every file, generates image derivatives, and is the ONLY place
 * that talks to storage. Business modules call this (or its Actions) and
 * reference the returned media id — they never touch storage, paths, validation,
 * or thumbnailing.
 *
 * Storage is delegated to Laravel filesystem disks, so new drivers (S3, R2,
 * Wasabi, GCS, Azure) are added purely through configuration.
 */
class MediaService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    /**
     * Validate + store a file and create its Media record.
     *
     * @param  array{collection?:string, visibility?:string, disk?:string, school_id?:int|null, metadata?:array<string,mixed>, mediable?:Model}  $options
     */
    public function upload(UploadedFile $file, array $options = []): Media
    {
        $checks = $this->validate($file);

        $visibility = $options['visibility'] ?? (string) config('media.default_visibility', 'private');
        $disk = $options['disk'] ?? $this->diskFor($visibility);
        $collection = $options['collection'] ?? 'default';

        $uuid = (string) Str::uuid();
        $extension = $checks['extension'];
        $storedName = $extension !== '' ? "{$uuid}.{$extension}" : $uuid;
        $directory = trim($collection, '/').'/'.now()->format('Y/m');

        $checksum = hash_file('sha256', (string) $file->getRealPath()) ?: null;
        [$width, $height] = $this->dimensions($file, $checks['category']);

        $path = $file->storeAs($directory, $storedName, [
            'disk' => $disk,
            'visibility' => $visibility === 'public' ? 'public' : 'private',
        ]);

        $metadata = $options['metadata'] ?? [];
        if ($checks['category'] === 'image') {
            $thumbnails = $this->generateDerivatives($disk, (string) $path, $uuid, $directory);
            if ($thumbnails !== []) {
                $metadata['thumbnails'] = $thumbnails;
            }
        }

        $media = new Media([
            'uuid' => $uuid,
            'school_id' => $options['school_id'] ?? Auth::user()?->school_id,
            'collection' => $collection,
            'disk' => $disk,
            'visibility' => $visibility,
            'path' => (string) $path,
            'filename' => $storedName,
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => $storedName,
            'extension' => $extension,
            'mime_type' => $checks['mime'],
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'checksum' => $checksum,
            'metadata' => $metadata === [] ? null : $metadata,
            'uploaded_by' => Auth::id(),
        ]);

        if (isset($options['mediable']) && $options['mediable'] instanceof Model) {
            $media->mediable_type = $options['mediable']::class;
            $media->mediable_id = $options['mediable']->getKey();
        }

        $media->save();

        $this->activity->record('media.uploaded', "Uploaded {$media->original_filename}", $media, [
            'disk' => $disk, 'visibility' => $visibility, 'size' => $media->size,
        ], $media->school_id, 'media');

        return $media;
    }

    /**
     * Replace the file behind an existing media record. The id (and therefore
     * every foreign-key reference) is preserved; the previous file and all of
     * its derivatives are removed and the checksum/thumbnails regenerated.
     */
    public function replace(Media $media, UploadedFile $file): Media
    {
        $checks = $this->validate($file);

        $extension = $checks['extension'];
        $uuid = (string) Str::uuid();
        $storedName = $extension !== '' ? "{$uuid}.{$extension}" : $uuid;
        $directory = trim($media->collection, '/').'/'.now()->format('Y/m');

        $checksum = hash_file('sha256', (string) $file->getRealPath()) ?: null;
        [$width, $height] = $this->dimensions($file, $checks['category']);

        // Remove the old file + derivatives first (no orphans left behind).
        $this->deleteFiles($media);

        $path = $file->storeAs($directory, $storedName, [
            'disk' => $media->disk,
            'visibility' => $media->visibility === 'public' ? 'public' : 'private',
        ]);

        $metadata = $media->metadata ?? [];
        unset($metadata['thumbnails'], $metadata['thumbnail_path']);
        if ($checks['category'] === 'image') {
            $thumbnails = $this->generateDerivatives($media->disk, (string) $path, $uuid, $directory);
            if ($thumbnails !== []) {
                $metadata['thumbnails'] = $thumbnails;
            }
        }

        $media->fill([
            'path' => (string) $path,
            'filename' => $storedName,
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => $storedName,
            'extension' => $extension,
            'mime_type' => $checks['mime'],
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'checksum' => $checksum,
            'metadata' => $metadata === [] ? null : $metadata,
        ])->save();

        $this->activity->record('media.replaced', "Replaced {$media->original_filename}", $media, [], $media->school_id, 'media');

        return $media->refresh();
    }

    /**
     * Delete the stored file(s) and the media record. Refuses (with a business
     * validation error, never a 500) if the media is still referenced by any
     * business data, so references are never broken.
     */
    public function delete(Media $media): bool
    {
        if (($where = $this->referencedBy($media)) !== null) {
            throw BusinessRuleException::make(
                "This file is still in use ({$where}) and cannot be deleted.",
                'MEDIA_IN_USE',
            );
        }

        $this->deleteFiles($media);
        $this->activity->record('media.deleted', "Deleted {$media->original_filename}", $media, [], $media->school_id, 'media');

        return (bool) $media->delete();
    }

    /** Stream a media file (or one of its derivatives) — the single download path. */
    public function stream(Media $media, ?string $variant = null): StreamedResponse
    {
        $path = $media->path;
        if ($variant !== null && $variant !== '') {
            $thumbnails = (array) ($media->metadata['thumbnails'] ?? []);
            $path = $thumbnails[$variant] ?? $path;
        }

        return Storage::disk($media->disk)->download($path, $media->original_filename);
    }

    /**
     * The first reference to this media in business data, or null if unused.
     */
    public function referencedBy(Media $media): ?string
    {
        foreach ((array) config('media.references', []) as $ref) {
            $table = $ref['table'] ?? null;
            if (! is_string($table) || ! Schema::hasTable($table)) {
                continue;
            }
            foreach ((array) ($ref['columns'] ?? []) as $column) {
                if (Schema::hasColumn($table, $column)
                    && DB::table($table)->where($column, $media->getKey())->exists()) {
                    return "{$table}.{$column}";
                }
            }
        }

        return null;
    }

    /**
     * Validate a file against the configured rules. Returns the resolved
     * category/extension/mime on success; throws on any violation.
     *
     * @return array{category:string, extension:string, mime:string}
     */
    public function validate(UploadedFile $file): array
    {
        if (! $file->isValid()) {
            throw BusinessRuleException::make('The uploaded file is invalid.', 'MEDIA_INVALID_UPLOAD');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $mime = (string) ($file->getMimeType() ?: $file->getClientMimeType());

        // Hard security gate: never accept executable/script extensions.
        $blocked = (array) config('media.blocked_extensions', []);
        if ($extension === '' || in_array($extension, $blocked, true)) {
            throw BusinessRuleException::make("Files of type '.{$extension}' are not allowed.", 'MEDIA_BLOCKED_TYPE');
        }

        $category = $this->categoryFor($extension);
        if ($category === null) {
            throw BusinessRuleException::make("Files of type '.{$extension}' are not allowed.", 'MEDIA_UNSUPPORTED_TYPE');
        }

        $rules = (array) config("media.categories.{$category}");

        // Size: per-category limit, then the global ceiling.
        $sizeKb = (int) ceil($file->getSize() / 1024);
        $maxKb = min((int) ($rules['max_size_kb'] ?? PHP_INT_MAX), (int) config('media.max_size_kb', PHP_INT_MAX));
        if ($sizeKb > $maxKb) {
            throw BusinessRuleException::make("File is too large (max {$maxKb} KB).", 'MEDIA_TOO_LARGE');
        }

        // Images must really be images, and within the dimension limits.
        if ($category === 'image') {
            $info = @getimagesize((string) $file->getRealPath());
            if ($info === false || ! str_starts_with($mime, 'image/')) {
                throw BusinessRuleException::make('The file is not a valid image.', 'MEDIA_INVALID_IMAGE');
            }
            if ($info[0] > (int) config('media.image.max_width') || $info[1] > (int) config('media.image.max_height')) {
                throw BusinessRuleException::make('Image dimensions exceed the allowed maximum.', 'MEDIA_IMAGE_TOO_LARGE');
            }
        } else {
            // Documents/archives: detected MIME must be consistent with the category.
            $allowed = (array) ($rules['mimes'] ?? []);
            if ($allowed !== [] && ! in_array($mime, $allowed, true) && $mime !== 'application/octet-stream') {
                throw BusinessRuleException::make('The file content does not match its type.', 'MEDIA_MIME_MISMATCH');
            }
        }

        return ['category' => $category, 'extension' => $extension, 'mime' => $mime];
    }

    private function categoryFor(string $extension): ?string
    {
        foreach ((array) config('media.categories', []) as $category => $rules) {
            if (in_array($extension, (array) ($rules['extensions'] ?? []), true)) {
                return (string) $category;
            }
        }

        return null;
    }

    private function diskFor(string $visibility): string
    {
        $key = $visibility === 'public' ? 'public' : 'private';

        return (string) config("media.disks.{$key}", 'local');
    }

    /**
     * @return array{0:int|null,1:int|null}
     */
    private function dimensions(UploadedFile $file, string $category): array
    {
        if ($category !== 'image') {
            return [null, null];
        }

        $info = @getimagesize((string) $file->getRealPath());

        return $info === false ? [null, null] : [(int) $info[0], (int) $info[1]];
    }

    /**
     * Generate every configured image size into the dedicated derivatives
     * directory, leaving the original untouched.
     *
     * @return array<string, string> size => stored derivative path
     */
    private function generateDerivatives(string $disk, string $originalPath, string $uuid, string $directory): array
    {
        if (! config('media.image.derivatives_enabled', true) || ! function_exists('imagecreatetruecolor')) {
            return [];
        }

        $sizes = (array) config('media.image.sizes', []);
        if ($sizes === []) {
            return [];
        }

        $derivedDir = trim((string) config('media.image.derivatives_directory', 'derivatives'), '/')
            .'/'.$directory;

        $result = [];
        foreach ($sizes as $name => $dims) {
            $path = $this->makeDerivative(
                $disk,
                $originalPath,
                "{$derivedDir}/{$uuid}_{$name}.png",
                (int) ($dims['width'] ?? 320),
                (int) ($dims['height'] ?? 320),
            );
            if ($path !== null) {
                $result[(string) $name] = $path;
            }
        }

        return $result;
    }

    /** Best-effort GD resize; returns the stored derivative path or null. */
    private function makeDerivative(string $disk, string $sourcePath, string $targetPath, int $maxW, int $maxH): ?string
    {
        try {
            $contents = Storage::disk($disk)->get($sourcePath);
            if ($contents === null) {
                return null;
            }

            $src = @imagecreatefromstring($contents);
            if ($src === false) {
                return null;
            }

            $w = imagesx($src);
            $h = imagesy($src);
            $ratio = min($maxW / $w, $maxH / $h, 1);
            $tw = max(1, (int) ($w * $ratio));
            $th = max(1, (int) ($h * $ratio));

            $dst = imagecreatetruecolor($tw, $th);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $tw, $th, $w, $h);

            ob_start();
            imagepng($dst);
            $data = (string) ob_get_clean();
            imagedestroy($src);
            imagedestroy($dst);

            Storage::disk($disk)->put($targetPath, $data);

            return $targetPath;
        } catch (\Throwable) {
            return null;
        }
    }

    private function deleteFiles(Media $media): void
    {
        $disk = Storage::disk($media->disk);

        if (is_string($media->path) && $media->path !== '' && $disk->exists($media->path)) {
            $disk->delete($media->path);
        }

        $thumbnails = (array) ($media->metadata['thumbnails'] ?? []);
        // Backward-compatibility with a single legacy thumbnail_path.
        if (isset($media->metadata['thumbnail_path'])) {
            $thumbnails[] = $media->metadata['thumbnail_path'];
        }
        foreach ($thumbnails as $thumb) {
            if (is_string($thumb) && $thumb !== '' && $disk->exists($thumb)) {
                $disk->delete($thumb);
            }
        }
    }
}
