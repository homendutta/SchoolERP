<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Enums;

/** Lifecycle of a webhook delivery (retried on the queue). */
enum DeliveryStatus: string
{
    case Pending = 'pending';
    case Delivered = 'delivered';
    case Failed = 'failed';

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
