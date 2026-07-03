<?php

declare(strict_types=1);

namespace App\Modules\Documents\Enums;

/** Lifecycle of a GENERATED document. Content is immutable; only status may move. */
enum DocumentStatus: string
{
    case Generated = 'generated';
    case Issued = 'issued';
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
