<?php

declare(strict_types=1);

namespace App\Modules\Documents\Http\Controllers;

use App\Modules\Documents\Services\VerificationService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public document verification — no login. Returns only the verification status +
 * basic, non-sensitive document details. Accepts QR (identity), document number or
 * verification code.
 */
class PublicVerificationController extends BaseController
{
    public function __construct(private readonly VerificationService $service) {}

    public function verify(Request $request): JsonResponse
    {
        $v = $request->validate([
            'method' => ['nullable', 'in:qr,document_number,code'],
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        return $this->ok($this->service->verify($v['method'] ?? 'auto', $v['identifier']));
    }
}
