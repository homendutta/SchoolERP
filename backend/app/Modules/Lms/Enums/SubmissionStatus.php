<?php

declare(strict_types=1);

namespace App\Modules\Lms\Enums;

/** Lifecycle of a student submission (history is immutable — a change adds a row). */
enum SubmissionStatus: string
{
    case Submitted = 'submitted';
    case Late = 'late';
    case Returned = 'returned';
    case Approved = 'approved';
    case Graded = 'graded';

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
