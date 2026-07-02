<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Enums;

/** Who a hostel accommodates. */
enum HostelGender: string
{
    case Boys = 'boys';
    case Girls = 'girls';
    case CoEd = 'co_ed';

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
