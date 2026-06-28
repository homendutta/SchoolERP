<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Modules\Examination\Services\ReportCardService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Report-card DATA (no visual designer). Lists assigned subjects only. */
class ReportCardController extends BaseController
{
    public function __construct(private readonly ReportCardService $service) {}

    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exam_session_id' => ['required', 'integer'],
            'student_id' => ['required', 'integer'],
        ]);

        return $this->ok($this->service->forStudent((int) $validated['exam_session_id'], (int) $validated['student_id']));
    }
}
