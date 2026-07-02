<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

/**
 * Depreciation method — stored as METADATA only. Inventory never performs
 * accounting calculations; Finance consumes this configuration later.
 */
enum DepreciationMethod: string
{
    case None = 'none';
    case StraightLine = 'straight_line';
    case WrittenDownValue = 'written_down_value';

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
