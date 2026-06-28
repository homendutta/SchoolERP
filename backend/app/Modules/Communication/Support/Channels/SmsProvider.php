<?php

declare(strict_types=1);

namespace App\Modules\Communication\Support\Channels;

use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Models\CommunicationMessage;
use App\Platform\Foundation\Notifications\NotificationService;

/**
 * Default SMS provider. Reuses the Platform Notification Engine. A real gateway
 * (Twilio / MSG91 / Textlocal / Fast2SMS) binds behind it later.
 */
class SmsProvider implements ChannelProvider
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function channel(): CommunicationChannel
    {
        return CommunicationChannel::Sms;
    }

    public function send(CommunicationMessage $message): bool
    {
        if (empty($message->address)) {
            return false;
        }

        $outbox = $this->notifications->sms(
            (string) $message->address,
            (string) $message->body,
            true,
            $message->school_id,
        );

        return $outbox->status === 'sent';
    }
}
