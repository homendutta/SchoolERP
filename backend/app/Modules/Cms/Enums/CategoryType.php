<?php

declare(strict_types=1);

namespace App\Modules\Cms\Enums;

/** The content family a CMS category applies to. */
enum CategoryType: string
{
    case Notice = 'notice';
    case News = 'news';
    case Gallery = 'gallery';
    case Video = 'video';
    case Download = 'download';

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
