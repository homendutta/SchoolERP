<?php

declare(strict_types=1);

namespace App\Modules\Documents\Http\Controllers;

use App\Modules\Documents\Models\GeneratedDocument;
use App\Modules\Documents\Services\VerificationService;
use App\Platform\Foundation\Identity\IdentityService;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Http\Controllers\BaseController;
use App\Platform\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/** Admin verification + dynamic QR (QR images are generated on the fly, never stored). */
class VerificationController extends BaseController
{
    public function __construct(
        private readonly VerificationService $service,
        private readonly IdentityService $identities,
    ) {}

    public function verify(Request $request): JsonResponse
    {
        $v = $request->validate([
            'method' => ['required', 'in:qr,document_number,code'],
            'identifier' => ['required', 'string'],
        ]);

        return $this->ok($this->service->verify($v['method'], $v['identifier']));
    }

    /** Dynamically render the document's QR as SVG (reuses the Identity Platform). */
    public function qr(int|string $id): Response|JsonResponse
    {
        $document = GeneratedDocument::query()->findOrFail($id);
        $identity = $document->identity_id !== null ? Identity::query()->find($document->identity_id) : null;
        if ($identity === null) {
            return ApiResponse::error('This document has no verification identity.', 404, 'NO_IDENTITY');
        }

        return response($this->identities->qrSvg($identity), 200, ['Content-Type' => 'image/svg+xml']);
    }
}
