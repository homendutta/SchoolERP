<?php

declare(strict_types=1);

namespace App\Modules\System\Enums;

/** Lifecycle of a backup run. */
enum BackupStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Verified = 'verified';

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
