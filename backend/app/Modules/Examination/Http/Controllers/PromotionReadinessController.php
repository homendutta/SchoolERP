<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Modules\Examination\Services\PromotionReadinessService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Promotion readiness (NOT automatic promotion). */
class PromotionReadinessController extends BaseController
{
    public function __construct(private readonly PromotionReadinessService $service) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exam_session_id' => ['required', 'integer'],
            'class_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'],
        ]);

        return $this->ok($this->service->forSession(
            (int) $validated['exam_session_id'],
            isset($validated['class_id']) ? (int) $validated['class_id'] : null,
            isset($validated['section_id']) ? (int) $validated['section_id'] : null,
        ));
    }
}
