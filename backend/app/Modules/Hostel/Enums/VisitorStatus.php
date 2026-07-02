<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Enums;

/** Lifecycle of a hostel visitor record. */
enum VisitorStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';
    case Rejected = 'rejected';

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
