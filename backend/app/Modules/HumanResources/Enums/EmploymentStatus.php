<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Enums;

/**
 * The state of an EMPLOYMENT record (not the employee). Employment changes over
 * time; every change creates a new record and history is never overwritten.
 */
enum EmploymentStatus: string
{
    case Active = 'active';
    case OnLeave = 'on_leave';
    case Suspended = 'suspended';
    case Separated = 'separated';

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
