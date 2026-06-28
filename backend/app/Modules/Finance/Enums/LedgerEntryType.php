<?php

declare(strict_types=1);

namespace App\Modules\Finance\Enums;

/** Accounting direction of a ledger entry. The ledger is independent of payments. */
enum LedgerEntryType: string
{
    case Debit = 'debit';
    case Credit = 'credit';

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
