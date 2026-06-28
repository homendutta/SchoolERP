<?php

declare(strict_types=1);

namespace App\Modules\Communication\Enums;

/**
 * Delivery channels. Never hardcoded at call sites — business modules pick a
 * channel (or let templates decide) and the engine routes via the channel
 * registry. The first four are active today; the rest are future-ready.
 */
enum CommunicationChannel: string
{
    case Email = 'email';
    case Sms = 'sms';
    case Push = 'push';
    case InApp = 'in_app';
    // Future-ready — registered as providers are added; no structural change.
    case Whatsapp = 'whatsapp';
    case Telegram = 'telegram';
    case Voice = 'voice';
    case Teams = 'teams';
    case Slack = 'slack';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    /** Channels with a shipped provider today. */
    public static function active(): array
    {
        return [self::Email, self::Sms, self::Push, self::InApp];
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
