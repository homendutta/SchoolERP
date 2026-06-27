<?php

declare(strict_types=1);

namespace App\Modules\Academic\Enums;

enum CalendarEventType: string
{
    case WorkingDay = 'working_day';
    case Holiday = 'holiday';
    case HalfDay = 'half_day';
    case ExaminationDay = 'examination_day';
    case SchoolEvent = 'school_event';
    case SpecialWorkingDay = 'special_working_day';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
