<?php

declare(strict_types=1);

namespace App\Modules\Students\Enums;

/**
 * The student lifecycle. Status is NEVER stored as free text — only these cases.
 */
enum StudentStatus: string
{
    case Applied = 'applied';
    case Enrolled = 'enrolled';
    case Active = 'active';
    case Promoted = 'promoted';
    case Transferred = 'transferred';
    case Withdrawn = 'withdrawn';
    case Graduated = 'graduated';
    case Archived = 'archived';

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
