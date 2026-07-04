<?php

declare(strict_types=1);

namespace App\Modules\Reports\Enums;

/** Lifecycle of an export request (queued for large/scheduled exports). */
enum ExportStatus: string
{
    case Queued = 'queued';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

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
