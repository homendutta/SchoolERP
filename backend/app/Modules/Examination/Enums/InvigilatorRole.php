<?php

declare(strict_types=1);

namespace App\Modules\Examination\Enums;

/** Role of a staff member assigned to invigilate an exam. */
enum InvigilatorRole: string
{
    case Chief = 'chief';
    case Assistant = 'assistant';

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
