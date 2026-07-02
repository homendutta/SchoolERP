<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

/** Result of a physical verification. Every verification is historical. */
enum VerificationStatus: string
{
    case Verified = 'verified';
    case Missing = 'missing';
    case Damaged = 'damaged';
    case Disposed = 'disposed';

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
