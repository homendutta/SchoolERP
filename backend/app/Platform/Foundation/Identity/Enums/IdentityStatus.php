<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Identity\Enums;

enum IdentityStatus: string
{
    case Active = 'active';
    case Disabled = 'disabled';

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
