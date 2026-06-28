<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Enums\ExamAttendanceStatus;
use App\Modules\Examination\Models\ExamAttendance;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/** Exam attendance (separate from daily attendance). */
class ExamAttendanceService extends BaseCrudService
{
    protected function model(): string
    {
        return ExamAttendance::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['student:id,name,admission_number']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'exam_schedule_id', 'student_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at'];
    }

    /**
     * Mark exam attendance for many students at once (upsert).
     *
     * @param  array<int, array{student_id:int, status:string, remarks?:string|null}>  $entries
     * @return array{marked:int}
     */
    public function markMany(int $schoolId, int $scheduleId, array $entries): array
    {
        return $this->transaction(function () use ($schoolId, $scheduleId, $entries): array {
            $marked = 0;
            foreach ($entries as $entry) {
                ExamAttendance::query()->updateOrCreate(
                    ['exam_schedule_id' => $scheduleId, 'student_id' => $entry['student_id']],
                    [
                        'school_id' => $schoolId,
                        'status' => ExamAttendanceStatus::from((string) $entry['status'])->value,
                        'remarks' => $entry['remarks'] ?? null,
                        'recorded_by' => Auth::id(),
                    ],
                );
                $marked++;
            }

            return ['marked' => $marked];
        });
    }
}
