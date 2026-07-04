<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Enums;

/** The last-known health of a provider. */
enum HealthStatus: string
{
    case Unknown = 'unknown';
    case Healthy = 'healthy';
    case Degraded = 'degraded';
    case Down = 'down';

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
