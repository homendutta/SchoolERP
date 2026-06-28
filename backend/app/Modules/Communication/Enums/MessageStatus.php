<?php

declare(strict_types=1);

namespace App\Modules\Communication\Enums;

/**
 * Lifecycle of a single queued message. Messages are never lost — a failed
 * message is retried (back to Pending) until it succeeds or exhausts attempts.
 */
enum MessageStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Read = 'read';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Delivered, self::Cancelled, self::Read], true);
    }
}
