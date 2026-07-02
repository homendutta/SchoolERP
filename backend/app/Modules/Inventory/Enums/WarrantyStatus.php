<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

/** Warranty status. Reminder events are published via the Communication Engine. */
enum WarrantyStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Void = 'void';

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
