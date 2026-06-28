<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Enums;

/**
 * Lifecycle of a raw biometric log as it flows through the connector → engine.
 */
enum BiometricProcessingStatus: string
{
    case Pending = 'pending';
    case Processed = 'processed';
    case Unmatched = 'unmatched'; // identity number not found
    case Failed = 'failed';

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
