<?php

declare(strict_types=1);

namespace App\Modules\Transport\Enums;

/** Daily operational status of a trip. */
enum TripStatus: string
{
    case Scheduled = 'scheduled';
    case Running = 'running';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

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
