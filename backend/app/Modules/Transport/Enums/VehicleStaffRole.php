<?php

declare(strict_types=1);

namespace App\Modules\Transport\Enums;

/**
 * Role of a Staff member assigned to a vehicle. Drivers and attendants are
 * always Staff (Staff Management is reused — no separate employee system).
 */
enum VehicleStaffRole: string
{
    case PrimaryDriver = 'primary_driver';
    case BackupDriver = 'backup_driver';
    case Attendant = 'attendant';
    case Helper = 'helper';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    public function isDriver(): bool
    {
        return in_array($this, [self::PrimaryDriver, self::BackupDriver], true);
    }
}
