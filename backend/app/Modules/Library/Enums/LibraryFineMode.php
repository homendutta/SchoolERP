<?php

declare(strict_types=1);

namespace App\Modules\Library\Enums;

/**
 * How a library fine accrues. Library CALCULATES fines; Finance collects the
 * payment (no payment logic lives here).
 */
enum LibraryFineMode: string
{
    case Daily = 'daily';
    case Flat = 'flat';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Fine for a number of late days at a per-unit amount. */
    public function compute(int $lateDays, float $amount): float
    {
        return match ($this) {
            self::Flat => $lateDays > 0 ? $amount : 0.0,
            self::Daily => $lateDays * $amount,
        };
    }
}
