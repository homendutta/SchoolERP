<?php

declare(strict_types=1);

namespace App\Modules\Finance\Enums;

/** How a discount/scholarship/sibling rule is computed. */
enum DiscountMethod: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Resolve the discount amount against a base amount. */
    public function compute(float $value, float $base): float
    {
        return $this === self::Percentage ? round($base * $value / 100, 2) : min($value, $base);
    }
}
