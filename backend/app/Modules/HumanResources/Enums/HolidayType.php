<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Enums;

/** Configurable holiday classification (nothing hardcoded). */
enum HolidayType: string
{
    case National = 'national';
    case State = 'state';
    case School = 'school';
    case Optional = 'optional';

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
