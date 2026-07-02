<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

/** Kind of stock movement. Movements are append-only; quantities never overwritten. */
enum MovementType: string
{
    case In = 'in';
    case Out = 'out';
    case Adjustment = 'adjustment';
    case Transfer = 'transfer';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Signed effect of a movement on current stock for a given quantity. */
    public function applyTo(float $current, float $quantity): float
    {
        return match ($this) {
            self::In => $current + $quantity,
            self::Out, self::Transfer => max(0, $current - $quantity),
            self::Adjustment => max(0, $quantity), // adjustment sets the level
        };
    }
}
