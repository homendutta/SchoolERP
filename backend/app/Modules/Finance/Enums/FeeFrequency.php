<?php

declare(strict_types=1);

namespace App\Modules\Finance\Enums;

/** How often a fee recurs. Never stored as free text. */
enum FeeFrequency: string
{
    case OneTime = 'one_time';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case HalfYearly = 'half_yearly';
    case Yearly = 'yearly';

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
