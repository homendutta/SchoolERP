<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Media\Models;

use App\Platform\Shared\Traits\HasUuid;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Platform media registry record. Modules reference media by id; the actual file
 * lives behind the storage abstraction (local now, S3/R2/GCS/Azure-ready). Never
 * construct these directly — go through the Media Service / Actions.
 */
class Media extends Model
{
    use HasUuid;

    protected $table = 'media';

    protected $fillable = [
        'uuid', 'school_id', 'collection', 'disk', 'visibility', 'path',
        'filename', 'original_filename', 'stored_filename', 'extension', 'mime_type', 'size',
        'width', 'height', 'duration', 'checksum', 'metadata',
        'mediable_type', 'mediable_id', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    /**
     * A resolvable URL for the file, or null if one cannot be produced.
     *
     * Public media on a URL-capable disk returns a direct URL; everything else
     * returns the authenticated download route so private files are never
     * exposed by guessable paths. Never throws — URL generation must never break
     * serialization.
     */
    public function url(): ?string
    {
        return $this->resolveUrl($this->path);
    }

    /** URL for a generated size (default "thumb"), or null. Never throws. */
    public function thumbnailUrl(string $size = 'thumb'): ?string
    {
        $thumbnails = (array) ($this->metadata['thumbnails'] ?? []);
        $path = $thumbnails[$size] ?? ($this->metadata['thumbnail_path'] ?? null);

        if (! is_string($path) || $path === '') {
            return null;
        }

        return $this->resolveUrl($path, $size);
    }

    private function resolveUrl(?string $path, ?string $variant = null): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        try {
            if ($this->isPublic()) {
                /** @var Filesystem $disk */
                $disk = Storage::disk($this->disk);
                if (method_exists($disk, 'url')) {
                    return $disk->url($path);
                }
            }

            $params = ['id' => $this->getKey()];
            if ($variant !== null) {
                $params['variant'] = $variant;
            }

            return route('media.download', $params);
        } catch (\Throwable) {
            return null;
        }
    }
}
