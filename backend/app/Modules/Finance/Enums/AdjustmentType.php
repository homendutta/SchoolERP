<?php

declare(strict_types=1);

namespace App\Modules\Finance\Enums;

/** Adjustment kinds. Adjustments always create independent records. */
enum AdjustmentType: string
{
    case CreditNote = 'credit_note';
    case DebitNote = 'debit_note';
    case Waiver = 'waiver';
    case Manual = 'manual';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    /** Credit notes & waivers reduce the student's due; debit notes increase it. */
    public function reducesDue(): bool
    {
        return in_array($this, [self::CreditNote, self::Waiver], true);
    }
}
