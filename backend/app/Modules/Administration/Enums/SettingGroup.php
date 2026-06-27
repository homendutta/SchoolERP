<?php

declare(strict_types=1);

namespace App\Modules\Administration\Enums;

enum SettingGroup: string
{
    case General = 'general';
    case Academic = 'academic';
    case Attendance = 'attendance';
    case Communication = 'communication';
    case Finance = 'finance';
    case Website = 'website';
    case Security = 'security';
    case Appearance = 'appearance';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $g) => $g->value, self::cases());
    }
}
