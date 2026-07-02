<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

/** Lifecycle of an asset assignment. History is never overwritten. */
enum AssignmentStatus: string
{
    case Active = 'active';
    case Returned = 'returned';
    case Transferred = 'transferred';

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
