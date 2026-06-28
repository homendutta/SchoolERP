<?php

declare(strict_types=1);

namespace App\Modules\Finance\Enums;

/** Refund coverage. Refunds never delete payments. */
enum RefundType: string
{
    case Full = 'full';
    case Partial = 'partial';

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
