<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Media\Http\Controllers;

use App\Platform\Foundation\Media\Actions\MediaDeleteAction;
use App\Platform\Foundation\Media\Actions\MediaReplaceAction;
use App\Platform\Foundation\Media\Actions\MediaUploadAction;
use App\Platform\Foundation\Media\Http\Requests\ReplaceMediaRequest;
use App\Platform\Foundation\Media\Http\Requests\UploadMediaRequest;
use App\Platform\Foundation\Media\Http\Resources\MediaResource;
use App\Platform\Foundation\Media\MediaService;
use App\Platform\Foundation\Media\Models\Media;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The shared Media endpoints. Thin: every operation delegates to a single
 * Action; no storage logic lives here.
 */
class MediaController extends BaseController
{
    public function upload(UploadMediaRequest $request, MediaUploadAction $action): JsonResponse
    {
        $media = $action->handle($request->file('file'), [
            'collection' => $request->input('collection', 'default'),
            'visibility' => $request->input('visibility'),
            'school_id' => $request->input('school_id'),
        ]);

        return $this->ok(new MediaResource($media), 'Uploaded.', 201);
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new MediaResource(Media::query()->findOrFail($id)));
    }

    public function replace(ReplaceMediaRequest $request, int|string $id, MediaReplaceAction $action): JsonResponse
    {
        $media = Media::query()->findOrFail($id);

        return $this->ok(new MediaResource($action->handle($media, $request->file('file'))), 'Replaced.');
    }

    public function destroy(int|string $id, MediaDeleteAction $action): JsonResponse
    {
        $action->handle(Media::query()->findOrFail($id));

        return $this->ok(null, 'Deleted.');
    }

    /** Stream the file (or one of its derivatives) for private/secure delivery. */
    public function download(Request $request, int|string $id, MediaService $service): StreamedResponse
    {
        $media = Media::query()->findOrFail($id);
        $variant = $request->query('variant');

        return $service->stream($media, is_string($variant) ? $variant : null);
    }
}
