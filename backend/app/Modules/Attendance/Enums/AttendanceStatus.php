<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Enums;

/**
 * The attendance state for a person/date/session. Never stored as free text.
 */
enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    case HalfDay = 'half_day';
    case Leave = 'leave';
    case Holiday = 'holiday';
    case Weekend = 'weekend';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    /** Statuses that count as "present" for percentage calculations. */
    public function countsAsPresent(): bool
    {
        return in_array($this, [self::Present, self::Late, self::HalfDay], true);
    }
}
