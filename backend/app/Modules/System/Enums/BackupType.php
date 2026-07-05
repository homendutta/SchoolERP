<?php

declare(strict_types=1);

namespace App\Modules\System\Enums;

/** What a backup manifest covers. */
enum BackupType: string
{
    case Database = 'database';
    case Media = 'media';
    case Config = 'config';
    case Full = 'full';

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
