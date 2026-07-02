<?php

declare(strict_types=1);

namespace App\Modules\Library\Enums;

/** Lifecycle of a physical copy. Copies are never deleted — history is kept. */
enum CopyStatus: string
{
    case Available = 'available';
    case Borrowed = 'borrowed';
    case Reserved = 'reserved';
    case Lost = 'lost';
    case Damaged = 'damaged';
    case UnderRepair = 'under_repair';
    case Withdrawn = 'withdrawn';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    /** Can this copy be borrowed right now? */
    public function isBorrowable(): bool
    {
        return $this === self::Available;
    }

    /** Statuses that block a renewal. */
    public function blocksRenewal(): bool
    {
        return in_array($this, [self::Lost, self::Damaged, self::Withdrawn, self::UnderRepair], true);
    }
}
