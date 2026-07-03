<?php

declare(strict_types=1);

namespace App\Modules\Cms\Enums;

/** Video source. Self-hosted references Media; external stores a URL only. */
enum VideoProvider: string
{
    case YouTube = 'youtube';
    case Vimeo = 'vimeo';
    case SelfHosted = 'self_hosted';

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
