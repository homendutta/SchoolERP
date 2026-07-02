<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Enums;

/** Hostel fee scope. Hostel defines fees; Finance manages billing/payment. */
enum HostelFeeType: string
{
    case Hostel = 'hostel';
    case SecurityDeposit = 'security_deposit';
    case Mess = 'mess';
    case Electricity = 'electricity';
    case Special = 'special';

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
