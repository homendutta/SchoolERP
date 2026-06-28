<?php

declare(strict_types=1);

namespace App\Modules\Students\Enums;

enum TransferType: string
{
    case Internal = 'internal'; // class/section change within the school
    case External = 'external'; // moving to another school

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
