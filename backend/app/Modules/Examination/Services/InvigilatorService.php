<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\ExamInvigilator;
use App\Modules\Examination\Models\ExamSchedule;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Invigilator (Staff) assignment to scheduled exams, with teacher-clash guard. */
class InvigilatorService extends BaseCrudService
{
    protected function model(): string
    {
        return ExamInvigilator::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['staff:id,name,employee_number', 'schedule:id,exam_date,period_id']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'exam_schedule_id', 'staff_id', 'role', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        $this->assertNoTeacherClash((int) $data['exam_schedule_id'], (int) $data['staff_id']);

        return parent::create($data);
    }

    /** A staff member cannot invigilate two exams at the same date+period. */
    private function assertNoTeacherClash(int $scheduleId, int $staffId): void
    {
        $schedule = ExamSchedule::query()->find($scheduleId);
        if ($schedule === null || $schedule->period_id === null) {
            return;
        }

        $clash = ExamInvigilator::query()
            ->where('staff_id', $staffId)
            ->whereHas('schedule', function ($q) use ($schedule): void {
                $q->whereDate('exam_date', $schedule->exam_date)
                    ->where('period_id', $schedule->period_id)
                    ->where('id', '!=', $schedule->id);
            })
            ->exists();

        if ($clash) {
            throw BusinessRuleException::make('This staff member is already invigilating another exam at this time.', 'INVIGILATOR_CLASH');
        }
    }
}
