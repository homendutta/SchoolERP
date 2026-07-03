<?php

declare(strict_types=1);

namespace App\Modules\Cms\Enums;

/** Where a CMS menu renders on the public website. */
enum MenuLocation: string
{
    case Header = 'header';
    case Footer = 'footer';
    case QuickLinks = 'quick_links';

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
