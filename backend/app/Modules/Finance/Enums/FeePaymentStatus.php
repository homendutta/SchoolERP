<?php

declare(strict_types=1);

namespace App\Modules\Finance\Enums;

/** Payment progress of a student fee / line item / installment. */
enum FeePaymentStatus: string
{
    case Pending = 'pending';
    case Partial = 'partial';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Waived = 'waived';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Derive status from amounts (overdue handled separately by due tracking). */
    public static function fromAmounts(float $net, float $paid): self
    {
        if ($paid <= 0) {
            return self::Pending;
        }

        return $paid >= $net ? self::Paid : self::Partial;
    }
}
