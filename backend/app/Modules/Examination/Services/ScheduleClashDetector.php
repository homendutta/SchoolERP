<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\ExamSchedule;
use App\Modules\Examination\Models\ExamSubject;
use App\Platform\Shared\Exceptions\BusinessRuleException;

/**
 * Prevents invalid exam schedules within a session, scoped by date + period:
 *   - ROOM_CLASH  : a room booked for two exams at the same date+period
 *   - CLASS_CLASH : a class/section with two exams at the same date+period
 * Teacher (invigilator) clashes are checked at assignment time.
 */
class ScheduleClashDetector
{
    /**
     * @param  array{exam_session_id:int, exam_subject_id:int, exam_date:string, period_id?:int|null, room_id?:int|null}  $data
     */
    public function assertNoClash(array $data, ?int $ignoreId = null): void
    {
        $periodId = $data['period_id'] ?? null;
        if ($periodId === null) {
            return; // period-less schedules are not clash-checked
        }

        $base = ExamSchedule::query()
            ->where('exam_session_id', $data['exam_session_id'])
            ->whereDate('exam_date', $data['exam_date'])
            ->where('period_id', $periodId)
            ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId));

        if (! empty($data['room_id'])) {
            $roomClash = (clone $base)->where('room_id', $data['room_id'])->exists();
            if ($roomClash) {
                throw BusinessRuleException::make('This room is already booked for another exam at this time.', 'ROOM_CLASH');
            }
        }

        $subject = ExamSubject::query()->find($data['exam_subject_id']);
        if ($subject !== null) {
            $classClash = (clone $base)
                ->whereHas('examSubject', function ($q) use ($subject): void {
                    $q->where('class_id', $subject->class_id)
                        ->when(
                            $subject->section_id === null,
                            fn ($s) => $s->whereNull('section_id'),
                            fn ($s) => $s->where('section_id', $subject->section_id),
                        );
                })
                ->exists();
            if ($classClash) {
                throw BusinessRuleException::make('This class already has an exam scheduled at this time.', 'CLASS_CLASH');
            }
        }
    }
}
