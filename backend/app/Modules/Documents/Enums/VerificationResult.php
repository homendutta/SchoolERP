<?php

declare(strict_types=1);

namespace App\Modules\Documents\Enums;

/** Outcome of a public/admin document verification. */
enum VerificationResult: string
{
    case Valid = 'valid';
    case Invalid = 'invalid';
    case Revoked = 'revoked';

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
