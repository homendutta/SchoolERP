<?php

declare(strict_types=1);

namespace App\Modules\Communication\Enums;

/** Configurable retry backoff. No hardcoded delays at call sites. */
enum BackoffStrategy: string
{
    case Fixed = 'fixed';
    case Linear = 'linear';
    case Exponential = 'exponential';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Delay (seconds) before the given attempt number (1-based). */
    public function delayFor(int $attempt, int $baseSeconds): int
    {
        return match ($this) {
            self::Fixed => $baseSeconds,
            self::Linear => $baseSeconds * max(1, $attempt),
            self::Exponential => $baseSeconds * (2 ** max(0, $attempt - 1)),
        };
    }
}
