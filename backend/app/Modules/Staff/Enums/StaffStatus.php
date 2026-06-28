<?php

declare(strict_types=1);

namespace App\Modules\Staff\Enums;

/**
 * The employee lifecycle. Status is NEVER stored as free text — only these cases.
 */
enum StaffStatus: string
{
    case Applicant = 'applicant';
    case Active = 'active';
    case OnLeave = 'on_leave';
    case Suspended = 'suspended';
    case Resigned = 'resigned';
    case Retired = 'retired';
    case Terminated = 'terminated';
    case Archived = 'archived';

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
