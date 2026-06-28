<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Modules\Finance\Support\Gateway\GatewayRegistry;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Online payment gateway — abstraction only. Lists configured providers and
 * initiates an order through the vendor-independent registry (no real gateway
 * integration ships).
 */
class GatewayController extends BaseController
{
    public function __construct(private readonly GatewayRegistry $registry) {}

    public function providers(): JsonResponse
    {
        return $this->ok(['providers' => $this->registry->providers()]);
    }

    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'student_id' => ['nullable', 'integer'],
            'reference' => ['nullable', 'string'],
        ]);

        $gateway = $this->registry->get($validated['provider'] ?? 'manual');

        return $this->ok($gateway->initiate([
            'amount' => (float) $validated['amount'],
            'student_id' => isset($validated['student_id']) ? (int) $validated['student_id'] : null,
            'reference' => $validated['reference'] ?? null,
        ]), 'Payment order initiated.');
    }
}
