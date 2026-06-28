<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\ExamSchedule;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Exam schedule built on the Timetable infrastructure (periods + rooms). */
class ExamScheduleService extends BaseCrudService
{
    public function __construct(
        private readonly ScheduleClashDetector $clashes,
        private readonly ActivityLogger $activity,
    ) {}

    protected function model(): string
    {
        return ExamSchedule::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'examSubject.subject:id,name,code',
            'examSubject.schoolClass:id,name',
            'examSubject.section:id,name',
            'period:id,name',
            'room:id,name,capacity',
            'invigilators.staff:id,name',
        ]);
    }

    protected function filterable(): array
    {
        return ['school_id', 'exam_session_id', 'exam_subject_id', 'exam_date', 'period_id', 'room_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'exam_date', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'exam_date' => ['type' => 'date'],
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        /** @var array{exam_session_id:int, exam_subject_id:int, exam_date:string, period_id?:int|null, room_id?:int|null} $data */
        $this->clashes->assertNoClash($data);
        $schedule = parent::create($data);
        $this->activity->record('exam.scheduled', 'Exam scheduled', $schedule, [], $schedule->getAttribute('school_id'), 'examination');

        return $schedule;
    }

    /** @param array<string, mixed> $data */
    public function update(Model $model, array $data): Model
    {
        $examDate = $data['exam_date'] ?? $model->getAttribute('exam_date');
        $merged = [
            'exam_session_id' => (int) ($data['exam_session_id'] ?? $model->getAttribute('exam_session_id')),
            'exam_subject_id' => (int) ($data['exam_subject_id'] ?? $model->getAttribute('exam_subject_id')),
            'exam_date' => $examDate instanceof \DateTimeInterface ? $examDate->format('Y-m-d') : (string) $examDate,
            'period_id' => $data['period_id'] ?? $model->getAttribute('period_id'),
            'room_id' => $data['room_id'] ?? $model->getAttribute('room_id'),
        ];
        $this->clashes->assertNoClash($merged, (int) $model->getKey());

        return parent::update($model, $data);
    }
}
