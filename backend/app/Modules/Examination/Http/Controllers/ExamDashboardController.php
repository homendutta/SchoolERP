<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Modules\Examination\Services\ExamDashboardService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamDashboardController extends BaseController
{
    public function __construct(private readonly ExamDashboardService $service) {}

    public function overview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => ['nullable', 'integer'],
            'exam_session_id' => ['nullable', 'integer'],
        ]);

        return $this->ok($this->service->overview(
            isset($validated['school_id']) ? (int) $validated['school_id'] : null,
            isset($validated['exam_session_id']) ? (int) $validated['exam_session_id'] : null,
        ));
    }
}
