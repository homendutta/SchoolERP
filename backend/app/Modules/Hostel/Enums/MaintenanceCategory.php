<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Enums;

/** Category of a hostel maintenance request. */
enum MaintenanceCategory: string
{
    case Electrical = 'electrical';
    case Plumbing = 'plumbing';
    case Furniture = 'furniture';
    case Cleaning = 'cleaning';
    case Security = 'security';
    case Other = 'other';

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
