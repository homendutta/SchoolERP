<?php

declare(strict_types=1);

namespace App\Modules\Transport\Enums;

/** Kind of transport vehicle. */
enum VehicleType: string
{
    case Bus = 'bus';
    case MiniBus = 'mini_bus';
    case Van = 'van';
    case Car = 'car';
    case Auto = 'auto';

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
