<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Enums;

/** How an employment ends. Separated employees are never deleted. */
enum SeparationType: string
{
    case Resignation = 'resignation';
    case Retirement = 'retirement';
    case Termination = 'termination';
    case ContractCompletion = 'contract_completion';
    case Death = 'death';

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
