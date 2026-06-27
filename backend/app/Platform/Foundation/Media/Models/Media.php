<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Media\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Platform media registry record. Modules reference media by id; the actual
 * file lives behind the storage abstraction (local now, cloud/CDN-ready).
 */
class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'collection', 'disk', 'path', 'filename', 'mime_type', 'size',
        'mediable_type', 'mediable_id', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return ['size' => 'integer'];
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
