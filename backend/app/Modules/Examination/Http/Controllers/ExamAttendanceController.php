<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Modules\Examination\Http\Requests\ExamAttendanceRequest;
use App\Modules\Examination\Http\Resources\ExamAttendanceResource;
use App\Modules\Examination\Services\ExamAttendanceService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Exam attendance — separate from daily attendance. */
class ExamAttendanceController extends BaseController
{
    public function __construct(private readonly ExamAttendanceService $service) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->service->list($request->all());

        return $this->ok(ExamAttendanceResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    /** Bulk-mark exam attendance for a scheduled exam. */
    public function mark(ExamAttendanceRequest $request): JsonResponse
    {
        /** @var array{school_id:int, exam_schedule_id:int, entries:array<int, array{student_id:int, status:string, remarks?:string|null}>} $data */
        $data = $request->validated();

        return $this->ok(
            $this->service->markMany($data['school_id'], $data['exam_schedule_id'], $data['entries']),
            'Attendance recorded.',
        );
    }
}
