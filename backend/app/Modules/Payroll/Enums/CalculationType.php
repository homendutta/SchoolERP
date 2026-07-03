<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Enums;

/** How a component value is derived. `formula` is future-ready (stored, not evaluated). */
enum CalculationType: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';
    case Formula = 'formula';

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
