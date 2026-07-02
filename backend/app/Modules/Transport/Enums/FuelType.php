<?php

declare(strict_types=1);

namespace App\Modules\Transport\Enums;

/** Vehicle fuel type. */
enum FuelType: string
{
    case Diesel = 'diesel';
    case Petrol = 'petrol';
    case Cng = 'cng';
    case Electric = 'electric';
    case Hybrid = 'hybrid';

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
