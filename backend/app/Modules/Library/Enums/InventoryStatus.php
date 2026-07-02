<?php

declare(strict_types=1);

namespace App\Modules\Library\Enums;

/** Result of an inventory verification for a copy. */
enum InventoryStatus: string
{
    case Verified = 'verified';
    case Missing = 'missing';
    case Misplaced = 'misplaced';
    case Damaged = 'damaged';

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
