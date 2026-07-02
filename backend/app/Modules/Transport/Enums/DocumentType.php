<?php

declare(strict_types=1);

namespace App\Modules\Transport\Enums;

/** Vehicle document types (stored as Media references, never files here). */
enum DocumentType: string
{
    case Insurance = 'insurance';
    case Registration = 'registration';
    case Pollution = 'pollution';
    case Fitness = 'fitness';
    case Permit = 'permit';

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
