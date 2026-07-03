<?php

declare(strict_types=1);

namespace App\Modules\Cms\Enums;

/** Notice priority (drives ordering + highlighting on the public site). */
enum NoticePriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';

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
