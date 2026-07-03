<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Enums;

/** A payroll run's state. Once Locked it is immutable; corrections need a new run. */
enum PayrollRunStatus: string
{
    case Draft = 'draft';
    case Processing = 'processing';
    case Completed = 'completed';
    case Locked = 'locked';
    case Cancelled = 'cancelled';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
