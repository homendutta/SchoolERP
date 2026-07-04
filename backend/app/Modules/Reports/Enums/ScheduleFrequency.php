<?php

declare(strict_types=1);

namespace App\Modules\Reports\Enums;

use Illuminate\Support\Carbon;

/** Cadence for a scheduled report. */
enum ScheduleFrequency: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function next(\DateTimeInterface $from): Carbon
    {
        $base = Carbon::instance(Carbon::parse($from));

        return match ($this) {
            self::Daily => $base->addDay(),
            self::Weekly => $base->addWeek(),
            self::Monthly => $base->addMonth(),
        };
    }
}
