<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Enums;

/** Exit-clearance progress recorded on a separation. */
enum ClearanceStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';

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
