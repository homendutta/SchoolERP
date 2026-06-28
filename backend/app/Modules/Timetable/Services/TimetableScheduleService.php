<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Services;

use App\Modules\Timetable\Enums\Weekday;
use App\Modules\Timetable\Models\ClassTimetable;
use App\Modules\Timetable\Models\TimetableSpecialEvent;
use App\Modules\Timetable\Models\TimetableSubstitution;
use Illuminate\Support\Carbon;

/**
 * Reusable read API over the timetable for OTHER modules to consume — the
 * timetable is the single source of truth for the academic schedule.
 *
 * Attendance (and, in the future, Subject Attendance, Examination scheduling,
 * Homework, Lesson Planning, the Parent Portal …) resolve the scheduled
 * periods/subjects for a class on a given date through this service. It does NOT
 * implement Subject Attendance — it only exposes the schedule information those
 * consumers need, applying same-day substitutions so the effective teacher is
 * correct.
 */
class TimetableScheduleService
{
    public function __construct(private readonly DerivedTimetableService $derived) {}

    /**
     * The effective schedule for a class+section on a calendar date: the master
     * slots for that weekday, with any same-day substitution applied, and a flag
     * if a special event cancels classes.
     *
     * @return array{
     *     date:string,
     *     weekday:string,
     *     cancelled:bool,
     *     special_event:array<string, mixed>|null,
     *     slots:array<int, array<string, mixed>>
     * }
     */
    public function forClassOnDate(
        int $schoolId,
        int $classId,
        ?int $sectionId,
        int $academicYearId,
        string $date,
        ?int $templateId = null,
    ): array {
        $carbon = Carbon::parse($date);
        $weekday = Weekday::from(strtolower($carbon->format('l')));

        $event = $this->cancellingEvent($schoolId, $classId, $sectionId, $date);

        $substitutions = TimetableSubstitution::query()
            ->where('school_id', $schoolId)
            ->whereDate('date', $date)
            ->where('class_id', $classId)
            ->where('status', '!=', 'cancelled')
            ->get()
            ->keyBy('period_id');

        $slots = $this->derived
            ->forClass($classId, $sectionId, $academicYearId, $templateId)
            ->where('weekday', $weekday)
            ->map(function (ClassTimetable $slot) use ($substitutions): array {
                $sub = $substitutions->get($slot->period_id);

                return [
                    'period_id' => $slot->period_id,
                    'period' => $slot->period?->name,
                    'subject_id' => $slot->subject_id,
                    'subject' => $slot->subject?->name,
                    'teacher_id' => $sub?->substitute_teacher_id ?? $slot->teacher_id,
                    'is_substituted' => $sub !== null,
                    'room_id' => $slot->room_id,
                ];
            })
            ->values()
            ->all();

        return [
            'date' => $carbon->toDateString(),
            'weekday' => $weekday->value,
            'cancelled' => $event !== null && $event->cancels_classes,
            'special_event' => $event?->only(['id', 'name', 'event_type', 'cancels_classes']),
            'slots' => $slots,
        ];
    }

    private function cancellingEvent(int $schoolId, int $classId, ?int $sectionId, string $date): ?TimetableSpecialEvent
    {
        return TimetableSpecialEvent::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $date)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $date))
            ->where(function ($q) use ($classId, $sectionId): void {
                $q->where('scope', 'school')
                    ->orWhere(fn ($c) => $c->where('scope', 'class')->where('class_id', $classId))
                    ->orWhere(fn ($c) => $c->where('scope', 'section')->where('section_id', $sectionId));
            })
            ->orderByDesc('cancels_classes')
            ->first();
    }
}
