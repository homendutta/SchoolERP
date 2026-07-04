<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Enums;

/** Direction of a webhook: incoming (received) or outgoing (sent). */
enum WebhookDirection: string
{
    case Incoming = 'incoming';
    case Outgoing = 'outgoing';

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
