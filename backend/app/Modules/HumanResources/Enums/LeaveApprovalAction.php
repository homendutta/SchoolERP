<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Enums;

/** A decision recorded at one level of a multi-level leave approval. */
enum LeaveApprovalAction: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';

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
