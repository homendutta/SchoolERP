<?php

declare(strict_types=1);

namespace App\Modules\Documents\Enums;

/** Template paper size. */
enum PaperSize: string
{
    case A4 = 'a4';
    case A5 = 'a5';
    case Letter = 'letter';
    case Legal = 'legal';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return strtoupper($this->value);
    }
}
