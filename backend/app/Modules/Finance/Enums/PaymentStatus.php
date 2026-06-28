<?php

declare(strict_types=1);

namespace App\Modules\Finance\Enums;

/** Lifecycle of a payment transaction (a payment is never deleted). */
enum PaymentStatus: string
{
    case Completed = 'completed';
    case Pending = 'pending';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

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
