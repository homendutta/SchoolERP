<?php

declare(strict_types=1);

namespace App\Modules\Communication\Http\Resources;

use App\Modules\Communication\Models\ChannelSetting;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin ChannelSetting
 */
class ChannelSettingResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'channel' => $this->channel->value,
            'is_enabled' => $this->is_enabled,
            'provider' => $this->provider,
            'config' => $this->config,
            'max_attempts' => $this->max_attempts,
            'retry_delay_seconds' => $this->retry_delay_seconds,
            'backoff' => $this->backoff->value,
        ];
    }
}
