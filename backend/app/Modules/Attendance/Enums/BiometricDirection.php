<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Enums;

enum BiometricDirection: string
{
    case In = 'in';
    case Out = 'out';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
