<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Controllers;

use App\Modules\Examination\Http\Requests\AssignStudentRequest;
use App\Modules\Examination\Http\Requests\ExamSubjectRequest;
use App\Modules\Examination\Http\Resources\ExamSubjectResource;
use App\Modules\Examination\Models\ExamSubject;
use App\Modules\Examination\Services\ExamSubjectService;
use App\Modules\Examination\Services\StudentSubjectService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

class ExamSubjectController extends BaseCrudController
{
    public function __construct(
        private readonly ExamSubjectService $service,
        private readonly StudentSubjectService $assignments,
    ) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return ExamSubjectResource::class;
    }

    /** Creating a CORE subject auto-assigns it to the class's current students. */
    public function store(ExamSubjectRequest $request): JsonResponse
    {
        $subject = $this->service->create($request->validated());
        if ($subject instanceof ExamSubject && ! $subject->is_elective) {
            $this->assignments->autoAssignCore($subject);
        }

        return $this->ok(new ExamSubjectResource($subject), 'Created.', 201);
    }

    public function update(ExamSubjectRequest $request, int|string $id): JsonResponse
    {
        return $this->updated($id, $request->validated());
    }

    /** Assign a student to a subject (used for electives). */
    public function assignStudent(int|string $id, AssignStudentRequest $request): JsonResponse
    {
        $subject = ExamSubject::query()->findOrFail($id);
        $this->assignments->assign($subject, (int) $request->validated()['student_id']);

        return $this->ok(null, 'Student assigned.');
    }

    /** Unassign a student from a subject. */
    public function unassignStudent(int|string $id, AssignStudentRequest $request): JsonResponse
    {
        $subject = ExamSubject::query()->findOrFail($id);
        $this->assignments->unassign($subject, (int) $request->validated()['student_id']);

        return $this->ok(null, 'Student unassigned.');
    }

    /** List students assigned to this subject. */
    public function students(int|string $id): JsonResponse
    {
        $subject = ExamSubject::query()->findOrFail($id);
        $students = $subject->studentSubjects()->with('student:id,name,admission_number')->get()
            ->map(fn ($ss) => [
                'student_id' => $ss->student_id,
                'student' => $ss->student?->name,
                'admission_number' => $ss->student?->admission_number,
            ]);

        return $this->ok($students);
    }
}
