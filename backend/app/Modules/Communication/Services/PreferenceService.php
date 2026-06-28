<?php

declare(strict_types=1);

namespace App\Modules\Communication\Services;

use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Models\CommunicationPreference;

/**
 * Per-user channel preferences. Opt-out model: a channel is enabled unless the
 * user has explicitly disabled it. The engine respects this unless a message is
 * marked mandatory.
 */
class PreferenceService
{
    public function isEnabled(?int $userId, CommunicationChannel $channel): bool
    {
        if ($userId === null) {
            return true;
        }

        $pref = CommunicationPreference::query()
            ->where('user_id', $userId)
            ->where('channel', $channel->value)
            ->first();

        return $pref === null || $pref->is_enabled;
    }

    /** Set a user's preference for a channel (upsert). */
    public function set(int $userId, CommunicationChannel $channel, bool $enabled): CommunicationPreference
    {
        return CommunicationPreference::query()->updateOrCreate(
            ['user_id' => $userId, 'channel' => $channel->value],
            ['is_enabled' => $enabled],
        );
    }
}
