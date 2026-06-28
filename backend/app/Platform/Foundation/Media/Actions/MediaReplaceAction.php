<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Media\Actions;

use App\Platform\Foundation\Media\MediaService;
use App\Platform\Foundation\Media\Models\Media;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Http\UploadedFile;

/**
 * Replace the file behind an existing media record while keeping its id, so
 * every reference (photo_media_id, logo_media_id, …) stays valid.
 */
class MediaReplaceAction implements Action
{
    use AsAction;

    public function __construct(private readonly MediaService $service) {}

    public function handle(Media $media, UploadedFile $file): Media
    {
        return $this->service->replace($media, $file);
    }
}
