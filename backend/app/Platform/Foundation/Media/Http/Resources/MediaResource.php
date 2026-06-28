<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Media\Http\Resources;

use App\Platform\Foundation\Media\Models\Media;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin Media
 */
class MediaResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'school_id' => $this->school_id,
            'collection' => $this->collection,
            'disk' => $this->disk,
            'visibility' => $this->visibility,
            'original_filename' => $this->original_filename,
            'stored_filename' => $this->stored_filename,
            'extension' => $this->extension,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            'duration' => $this->duration,
            'checksum' => $this->checksum,
            'metadata' => $this->metadata,
            'uploaded_by' => $this->uploaded_by,
            'url' => $this->url(),
            'thumbnail_url' => $this->thumbnailUrl(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
