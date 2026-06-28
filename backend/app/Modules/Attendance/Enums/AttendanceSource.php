<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Enums;

/**
 * Where an attendance record originated. Stored on every record so business
 * modules never need to know the source — the engine treats them identically.
 */
enum AttendanceSource: string
{
    case Manual = 'manual';
    case Import = 'import';
    case Biometric = 'biometric';
    case Api = 'api';

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
