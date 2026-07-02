<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Enums;

/** Status of a bed (and, mirrored, a room). */
enum BedStatus: string
{
    case Available = 'available';
    case Occupied = 'occupied';
    case Reserved = 'reserved';
    case UnderMaintenance = 'under_maintenance';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    public function isAllocatable(): bool
    {
        return $this === self::Available;
    }
}
