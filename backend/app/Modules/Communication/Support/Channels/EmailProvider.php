<?php

declare(strict_types=1);

namespace App\Modules\Communication\Support\Channels;

use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Models\CommunicationMessage;
use App\Platform\Foundation\Notifications\NotificationService;

/**
 * Default email provider. Reuses the Platform Notification Engine for delivery
 * (no duplicated send logic). A real transport (SMTP / SES / Mailgun / SendGrid)
 * binds behind the Notification Engine later — this module stays unchanged.
 */
class EmailProvider implements ChannelProvider
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function channel(): CommunicationChannel
    {
        return CommunicationChannel::Email;
    }

    public function send(CommunicationMessage $message): bool
    {
        if (empty($message->address)) {
            return false;
        }

        $outbox = $this->notifications->email(
            (string) $message->address,
            (string) ($message->subject ?? ''),
            (string) $message->body,
            true,
            $message->school_id,
        );

        return $outbox->status === 'sent';
    }
}
