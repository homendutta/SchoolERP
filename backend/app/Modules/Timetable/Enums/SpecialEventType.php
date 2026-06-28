<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Enums;

/**
 * Category of a timetable override (special event). Overrides are stored
 * separately and never overwrite the master timetable.
 */
enum SpecialEventType: string
{
    case Holiday = 'holiday';
    case Event = 'event';
    case Exam = 'exam';
    case Festival = 'festival';
    case Function = 'function';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Whether this override cancels regular classes by default. */
    public function cancelsClassesByDefault(): bool
    {
        return in_array($this, [self::Holiday, self::Exam, self::Festival, self::Function], true);
    }
}
