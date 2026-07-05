<?php

declare(strict_types=1);

namespace App\Modules\System\Enums;

/** Health state of a single component + the overall system. */
enum HealthState: string
{
    case Ok = 'ok';
    case Warn = 'warn';
    case Down = 'down';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    /** A numeric weight used to compute the overall health score. */
    public function weight(): int
    {
        return match ($this) {
            self::Ok => 100,
            self::Warn => 60,
            self::Down => 0,
        };
    }
}
