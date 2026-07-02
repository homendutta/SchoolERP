<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Enums;

/** Warden role. Wardens are always Staff members. */
enum WardenRole: string
{
    case Chief = 'chief';
    case Assistant = 'assistant';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucfirst($this->value).' Warden';
    }
}
