<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Enums;

/** Type of disciplinary action. Complete history is maintained (never deleted). */
enum DisciplinaryAction: string
{
    case Warning = 'warning';
    case Suspension = 'suspension';
    case Notice = 'notice';
    case TerminationRecommendation = 'termination_recommendation';
    case Other = 'other';

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
