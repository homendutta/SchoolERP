<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Media\Actions;

use App\Platform\Foundation\Media\MediaService;
use App\Platform\Foundation\Media\Models\Media;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Http\UploadedFile;

/**
 * Upload a file through the shared pipeline. The single entry point every module
 * uses to turn an uploaded file into a media id.
 */
class MediaUploadAction implements Action
{
    use AsAction;

    public function __construct(private readonly MediaService $service) {}

    /**
     * @param  array<string, mixed>  $options
     */
    public function handle(UploadedFile $file, array $options = []): Media
    {
        return $this->service->upload($file, $options);
    }
}
