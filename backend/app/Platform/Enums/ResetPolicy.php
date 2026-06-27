<?php

declare(strict_types=1);

namespace App\Platform\Enums;

use Illuminate\Support\Carbon;

/** Sequence reset cadence, shared by the number generator and others. */
enum ResetPolicy: string
{
    case None = 'none';
    case Daily = 'daily';
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    /** The boundary key for the current period (null = never resets). */
    public function currentPeriod(?Carbon $at = null): ?string
    {
        $at ??= Carbon::now();

        return match ($this) {
            self::None => null,
            self::Daily => $at->format('Y-m-d'),
            self::Monthly => $at->format('Y-m'),
            self::Yearly => $at->format('Y'),
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
