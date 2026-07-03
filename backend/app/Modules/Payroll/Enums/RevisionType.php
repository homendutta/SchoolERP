<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Enums;

/** Why a salary was revised. Every revision creates a new immutable version. */
enum RevisionType: string
{
    case Promotion = 'promotion';
    case AnnualIncrement = 'annual_increment';
    case SpecialIncrement = 'special_increment';
    case Correction = 'correction';

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
