<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Enums;

/** Configurable statutory deduction type. Rates are NEVER hardcoded (config only). */
enum StatutoryType: string
{
    case ProvidentFund = 'pf';
    case Esi = 'esi';
    case ProfessionalTax = 'professional_tax';
    case Tds = 'tds';
    case Other = 'other';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::ProvidentFund => 'Provident Fund',
            self::Esi => 'ESI',
            self::ProfessionalTax => 'Professional Tax',
            self::Tds => 'TDS',
            self::Other => 'Other',
        };
    }
}
