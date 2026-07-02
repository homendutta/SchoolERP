<?php

declare(strict_types=1);

namespace App\Modules\Library\Enums;

/** Physical condition of a copy at acquisition / verification. */
enum CopyCondition: string
{
    case New = 'new';
    case Good = 'good';
    case Fair = 'fair';
    case Poor = 'poor';

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
