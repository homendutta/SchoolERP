<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Modules\Examination\Http\Requests\MarksRequest;
use App\Modules\Examination\Http\Resources\ExamMarkResource;
use App\Modules\Examination\Models\ExamMark;
use App\Modules\Examination\Models\ExamSubject;
use App\Modules\Examination\Services\MarksService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarksController extends BaseController
{
    public function __construct(private readonly MarksService $service) {}

    /** Marks for an exam subject (optionally filtered by student). */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exam_subject_id' => ['required', 'integer'],
            'student_id' => ['nullable', 'integer'],
        ]);

        $marks = ExamMark::query()
            ->with('student:id,name,admission_number')
            ->where('exam_subject_id', $validated['exam_subject_id'])
            ->when($validated['student_id'] ?? null, fn ($q, $sid) => $q->where('student_id', $sid))
            ->get();

        return $this->ok(ExamMarkResource::collection($marks));
    }

    /** Enter / autosave marks for many students of one subject. */
    public function store(MarksRequest $request): JsonResponse
    {
        /** @var array{exam_subject_id:int, entries:array<int, array{student_id:int, component_id?:int|null, marks_obtained?:float|null, is_absent?:bool, remarks?:string|null}>} $data */
        $data = $request->validated();
        $subject = ExamSubject::query()->findOrFail($data['exam_subject_id']);

        return $this->ok($this->service->enterMany($subject, $data['entries']), 'Marks saved.');
    }
}
