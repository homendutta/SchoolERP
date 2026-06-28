<?php

declare(strict_types=1);

namespace App\Modules\Communication\Support\Channels;

use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Models\CommunicationMessage;

/**
 * In-app provider. The message row itself IS the in-app notification (read via
 * the messages API), so delivery is immediate and always succeeds.
 */
class InAppProvider implements ChannelProvider
{
    public function channel(): CommunicationChannel
    {
        return CommunicationChannel::InApp;
    }

    public function send(CommunicationMessage $message): bool
    {
        return true;
    }
}
