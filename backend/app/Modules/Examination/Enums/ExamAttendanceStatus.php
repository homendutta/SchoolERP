<?php

declare(strict_types=1);

namespace App\Modules\Examination\Enums;

/**
 * Exam-day attendance state — SEPARATE from daily attendance. Never free text.
 */
enum ExamAttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Malpractice = 'malpractice';
    case MedicalLeave = 'medical_leave';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    /** Statuses that allow marks to count (present students only). */
    public function countsForMarks(): bool
    {
        return $this === self::Present;
    }
}
