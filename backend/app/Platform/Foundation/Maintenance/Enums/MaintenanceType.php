<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Maintenance\Enums;

/** Kind of maintenance. Shared by every module that consumes the engine. */
enum MaintenanceType: string
{
    case Preventive = 'preventive';
    case Corrective = 'corrective';
    case Emergency = 'emergency';

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
