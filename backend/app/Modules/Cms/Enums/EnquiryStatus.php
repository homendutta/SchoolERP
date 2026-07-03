<?php

declare(strict_types=1);

namespace App\Modules\Cms\Enums;

/** Lifecycle of a public enquiry / form submission (handled inside the ERP). */
enum EnquiryStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Responded = 'responded';
    case Closed = 'closed';

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
