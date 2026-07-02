<?php

declare(strict_types=1);

namespace App\Modules\Transport\Enums;

/** Operational status of a vehicle. */
enum VehicleStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case UnderMaintenance = 'under_maintenance';
    case Retired = 'retired';

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
