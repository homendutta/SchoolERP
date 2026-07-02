<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Enums;

/** Lifecycle of a bed allocation. History is never overwritten. */
enum AllocationStatus: string
{
    case Active = 'active';
    case CheckedOut = 'checked_out';
    case Transferred = 'transferred';
    case Cancelled = 'cancelled';

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
