<?php

declare(strict_types=1);

namespace App\Modules\Documents\Enums;

/** Template page orientation. */
enum Orientation: string
{
    case Portrait = 'portrait';
    case Landscape = 'landscape';

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
