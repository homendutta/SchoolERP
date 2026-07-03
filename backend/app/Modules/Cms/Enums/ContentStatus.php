<?php

declare(strict_types=1);

namespace App\Modules\Cms\Enums;

/** Publication state for CMS content. `scheduled` publishes at scheduled_at. */
enum ContentStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Scheduled = 'scheduled';
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
