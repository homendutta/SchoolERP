<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Enums;

/** Lifecycle of a loan / advance. Payroll deducts installments while active. */
enum LoanStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Closed = 'closed';

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
