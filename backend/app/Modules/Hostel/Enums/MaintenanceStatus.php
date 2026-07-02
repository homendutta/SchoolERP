<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Enums;

/** Status of a hostel maintenance request (no workflow engine). */
enum MaintenanceStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

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
