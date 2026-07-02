<?php

declare(strict_types=1);

namespace App\Modules\Library\Enums;

/** Lifecycle of a borrowing transaction. Return never overwrites the record. */
enum BorrowStatus: string
{
    case Borrowed = 'borrowed';
    case Returned = 'returned';
    case Overdue = 'overdue';
    case Lost = 'lost';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Borrowed, self::Overdue], true);
    }
}
