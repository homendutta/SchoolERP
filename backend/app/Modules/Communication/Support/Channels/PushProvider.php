<?php

declare(strict_types=1);

namespace App\Modules\Communication\Support\Channels;

use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Models\CommunicationMessage;
use Illuminate\Support\Facades\Log;

/**
 * Default push provider. The attempt is logged and treated as delivered so the
 * workflow stays observable and testable. A real transport (FCM / APNs) binds
 * later — no structural change.
 */
class PushProvider implements ChannelProvider
{
    public function channel(): CommunicationChannel
    {
        return CommunicationChannel::Push;
    }

    public function send(CommunicationMessage $message): bool
    {
        Log::info('communication.push', ['to' => $message->address, 'subject' => $message->subject]);

        return true;
    }
}
