<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Enums;

/**
 * Payroll-side settlement status of a payslip. Finance records the ACTUAL
 * payment; Payroll only records the salary-processing status.
 */
enum SettlementStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case PartiallyPaid = 'partially_paid';
    case Failed = 'failed';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
