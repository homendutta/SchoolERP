<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Enums;

/**
 * Lifecycle of a teacher substitution. Substitutions are separate records and
 * never modify the master timetable.
 */
enum SubstitutionStatus: string
{
    case Planned = 'planned';
    case Confirmed = 'confirmed';
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
