<?php

declare(strict_types=1);

namespace App\Platform\Shared\Traits;

use App\Platform\Foundation\Media\Models\Media;

/**
 * Helper for models that reference media by id (e.g., branding assets). Resolves
 * a media id to a public URL without coupling to a storage path string.
 */
trait InteractsWithMedia
{
    public function mediaUrl(?int $mediaId): ?string
    {
        if ($mediaId === null) {
            return null;
        }

        return Media::query()->find($mediaId)?->url();
    }
}
