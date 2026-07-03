<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Enums;

/** Employee loan vs salary advance (Finance owns the actual cash movement). */
enum LoanType: string
{
    case Loan = 'loan';
    case Advance = 'advance';

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
