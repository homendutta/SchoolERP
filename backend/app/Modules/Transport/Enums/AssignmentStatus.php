<?php

declare(strict_types=1);

namespace App\Modules\Transport\Enums;

/** Status of a student transport assignment. History is never deleted. */
enum AssignmentStatus: string
{
    case Active = 'active';
    case Transferred = 'transferred';
    case Cancelled = 'cancelled';

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
