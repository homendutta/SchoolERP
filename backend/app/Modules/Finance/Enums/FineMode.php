<?php

declare(strict_types=1);

namespace App\Modules\Finance\Enums;

/** How a fine accrues for an overdue fee. */
enum FineMode: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
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

    /** Number of accrual units for a given count of overdue days. */
    public function units(int $overdueDays): int
    {
        return match ($this) {
            self::Flat => $overdueDays > 0 ? 1 : 0,
            self::Daily => $overdueDays,
            self::Weekly => (int) ceil($overdueDays / 7),
            self::Monthly => (int) ceil($overdueDays / 30),
        };
    }
}
