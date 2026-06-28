<?php

declare(strict_types=1);

namespace App\Modules\Examination\Enums;

/** Lifecycle of an exam session. */
enum ExamSessionStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Published = 'published';

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
