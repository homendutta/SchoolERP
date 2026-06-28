<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Controllers;

use App\Modules\Timetable\Http\Resources\ClassTimetableResource;
use App\Modules\Timetable\Services\DerivedTimetableService;
use App\Modules\Timetable\Services\WorkloadService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Teacher timetable + workload — both DERIVED from the master class timetable.
 */
class TeacherTimetableController extends BaseController
{
    public function __construct(
        private readonly DerivedTimetableService $derived,
        private readonly WorkloadService $workload,
    ) {}

    /** School-wide teacher workload overview (drives the dashboard chart). */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', 'integer'],
            'academic_year_id' => ['required', 'integer'],
            'template_id' => ['nullable', 'integer'],
        ]);

        return $this->ok($this->workload->overview(
            (int) $validated['school_id'],
            (int) $validated['academic_year_id'],
            isset($validated['template_id']) ? (int) $validated['template_id'] : null,
        ));
    }

    /** One teacher's derived timetable grid + calculated workload. */
    public function show(int|string $teacherId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'integer'],
            'template_id' => ['nullable', 'integer'],
        ]);

        $yearId = (int) $validated['academic_year_id'];
        $templateId = isset($validated['template_id']) ? (int) $validated['template_id'] : null;

        return $this->ok([
            'slots' => ClassTimetableResource::collection($this->derived->forTeacher((int) $teacherId, $yearId, $templateId)),
            'workload' => $this->workload->forTeacher((int) $teacherId, $yearId, $templateId),
        ]);
    }
}
