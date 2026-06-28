<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Identity\Http\Controllers;

use App\Platform\Foundation\Identity\Actions\GenerateBarcodeAction;
use App\Platform\Foundation\Identity\Actions\GenerateQrImageAction;
use App\Platform\Foundation\Identity\Enums\IdentityStatus;
use App\Platform\Foundation\Identity\Http\Requests\RegenerateIdentityRequest;
use App\Platform\Foundation\Identity\Http\Resources\IdentityResource;
use App\Platform\Foundation\Identity\IdentityService;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Platform Identity endpoints. Thin: every operation delegates to the
 * IdentityService / Actions. QR + barcode images are rendered dynamically.
 */
class IdentityController extends BaseController
{
    public function __construct(private readonly IdentityService $service) {}

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new IdentityResource(Identity::query()->with('owner')->findOrFail($id)));
    }

    /** QR image (SVG) by default; `?format=payload` returns the data only. */
    public function qr(Request $request, int|string $id, GenerateQrImageAction $action): Response|JsonResponse
    {
        $identity = Identity::query()->findOrFail($id);

        if ($request->query('format') === 'payload') {
            return $this->ok($identity->qr_payload);
        }

        return response($action->handle($identity), 200, ['Content-Type' => 'image/svg+xml']);
    }

    /** Barcode image (SVG). */
    public function barcode(int|string $id, GenerateBarcodeAction $action): Response
    {
        $identity = Identity::query()->findOrFail($id);

        return response($action->handle($identity), 200, ['Content-Type' => 'image/svg+xml']);
    }

    /** Regenerate derived data (QR payload + barcode value); immutable fields untouched. */
    public function regenerate(RegenerateIdentityRequest $request): JsonResponse
    {
        $identity = $request->filled('identity_id')
            ? Identity::query()->findOrFail($request->integer('identity_id'))
            : Identity::query()->where('public_identifier', $request->string('public_identifier'))->firstOrFail();

        return $this->ok(new IdentityResource($this->service->regenerate($identity)->load('owner')), 'Identity regenerated.');
    }

    /** Enable / disable an identity (the only mutable aspect). */
    public function status(Request $request, int|string $id): JsonResponse
    {
        $data = $request->validate(['status' => ['required', 'in:active,disabled']]);
        $identity = Identity::query()->findOrFail($id);

        $updated = $this->service->setStatus($identity, IdentityStatus::from($data['status']));

        return $this->ok(new IdentityResource($updated->load('owner')), 'Identity status updated.');
    }

    public function search(Request $request): JsonResponse
    {
        $page = $this->service->search($request->all());

        return $this->ok(IdentityResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }
}
