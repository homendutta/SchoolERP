<?php

declare(strict_types=1);

namespace App\Modules\Communication\Support\Channels;

use App\Modules\Communication\Enums\CommunicationChannel;

/**
 * Registry of channel providers. New providers register here — the engine never
 * changes (mirrors the Attendance biometric connector & Finance gateway
 * patterns). Channels are never hardcoded at call sites.
 */
class ProviderRegistry
{
    /** @var array<string, ChannelProvider> */
    private array $providers = [];

    public function register(ChannelProvider $provider): void
    {
        $this->providers[$provider->channel()->value] = $provider;
    }

    public function has(CommunicationChannel $channel): bool
    {
        return isset($this->providers[$channel->value]);
    }

    public function get(CommunicationChannel $channel): ?ChannelProvider
    {
        return $this->providers[$channel->value] ?? null;
    }

    /** @return array<int, string> */
    public function channels(): array
    {
        return array_keys($this->providers);
    }
}
