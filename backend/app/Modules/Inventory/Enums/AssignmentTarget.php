<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

/** What an asset is assigned to. Assignments are historical. */
enum AssignmentTarget: string
{
    case Staff = 'staff';
    case Department = 'department';
    case Room = 'room';
    case Hostel = 'hostel';
    case Library = 'library';
    case Laboratory = 'laboratory';

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
