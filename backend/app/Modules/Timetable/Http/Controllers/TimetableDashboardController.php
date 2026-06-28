<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Controllers;

use App\Modules\Timetable\Services\TimetableDashboardService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimetableDashboardController extends BaseController
{
    public function __construct(private readonly TimetableDashboardService $service) {}

    public function overview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => ['nullable', 'integer'],
            'academic_year_id' => ['required', 'integer'],
            'template_id' => ['nullable', 'integer'],
        ]);

        return $this->ok($this->service->overview(
            isset($validated['school_id']) ? (int) $validated['school_id'] : null,
            (int) $validated['academic_year_id'],
            isset($validated['template_id']) ? (int) $validated['template_id'] : null,
        ));
    }
}
