<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Enums;

/** State of a performance review. Reviews are historical (never overwritten). */
enum ReviewStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
