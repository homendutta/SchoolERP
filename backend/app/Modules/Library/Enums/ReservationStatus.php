<?php

declare(strict_types=1);

namespace App\Modules\Library\Enums;

/** Reservation queue lifecycle. Queue order is preserved by queue_position. */
enum ReservationStatus: string
{
    case Pending = 'pending';    // waiting in queue
    case Available = 'available'; // copy ready for this borrower to collect
    case Fulfilled = 'fulfilled'; // borrower has borrowed the reserved copy
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Pending, self::Available], true);
    }
}
