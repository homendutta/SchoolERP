<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Modules\Finance\Services\DueTrackingService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Live due tracking (calculated, never snapshotted). */
class DueTrackingController extends BaseController
{
    public function __construct(private readonly DueTrackingService $service) {}

    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer'],
            'as_of' => ['nullable', 'date'],
        ]);

        return $this->ok($this->service->forStudent((int) $validated['student_id'], $validated['as_of'] ?? null));
    }
}
