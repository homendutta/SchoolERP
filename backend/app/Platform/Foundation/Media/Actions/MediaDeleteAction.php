<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Media\Actions;

use App\Platform\Foundation\Media\MediaService;
use App\Platform\Foundation\Media\Models\Media;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

/**
 * Delete a media record and its stored file(s) through the shared pipeline.
 */
class MediaDeleteAction implements Action
{
    use AsAction;

    public function __construct(private readonly MediaService $service) {}

    public function handle(Media $media): bool
    {
        return $this->service->delete($media);
    }
}
