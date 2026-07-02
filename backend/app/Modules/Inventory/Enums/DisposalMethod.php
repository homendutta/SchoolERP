<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

/** How an asset was disposed. Disposal history is never deleted. */
enum DisposalMethod: string
{
    case Sold = 'sold';
    case Scrapped = 'scrapped';
    case Donated = 'donated';
    case WrittenOff = 'written_off';

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
