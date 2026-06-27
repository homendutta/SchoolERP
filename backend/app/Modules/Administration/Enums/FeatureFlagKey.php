<?php

declare(strict_types=1);

namespace App\Modules\Administration\Enums;

enum FeatureFlagKey: string
{
    case Library = 'library';
    case Transport = 'transport';
    case Hostel = 'hostel';
    case Payroll = 'payroll';
    case Visitor = 'visitor';

    public function label(): string
    {
        return match ($this) {
            self::Library => 'Library',
            self::Transport => 'Transport',
            self::Hostel => 'Hostel',
            self::Payroll => 'Payroll',
            self::Visitor => 'Visitor Management',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $f) => $f->value, self::cases());
    }
}
