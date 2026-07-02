<?php

declare(strict_types=1);

namespace App\Modules\Transport\Enums;

/** Transport fee scope. Transport defines fees; Finance collects payment. */
enum TransportFeeType: string
{
    case Route = 'route';
    case Stop = 'stop';
    case Special = 'special';

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
