<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Enums;

/** The nature of a salary component / payslip line. */
enum ComponentType: string
{
    case Earning = 'earning';
    case Deduction = 'deduction';
    case EmployerContribution = 'employer_contribution';
    case Informational = 'informational';

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
