<?php

declare(strict_types=1);

namespace App\Modules\Communication\Support\Channels;

use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Models\CommunicationMessage;

/**
 * Vendor-independent channel provider. Concrete providers (SMTP / SES / Mailgun
 * / SendGrid for email; Twilio / MSG91 / Textlocal / Fast2SMS for SMS; FCM /
 * APNs for push; Meta / Twilio for WhatsApp …) implement this WITHOUT the rest
 * of the module knowing about them. Business modules stay provider-independent.
 */
interface ChannelProvider
{
    public function channel(): CommunicationChannel;

    /** Deliver the message. Return true on success; false/throw marks it failed. */
    public function send(CommunicationMessage $message): bool;
}
