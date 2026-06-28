<?php

declare(strict_types=1);

namespace App\Modules\Communication\Services;

use App\Modules\Communication\Models\ChannelSetting;
use App\Platform\Shared\Services\BaseCrudService;

class ChannelSettingService extends BaseCrudService
{
    protected function model(): string
    {
        return ChannelSetting::class;
    }

    protected function filterable(): array
    {
        return ['school_id', 'channel', 'is_enabled'];
    }

    protected function sortable(): array
    {
        return ['id', 'channel'];
    }

    /** Upsert a channel's settings (enable/provider/retry policy). */
    public function upsert(int $schoolId, string $channel, array $data): ChannelSetting
    {
        return ChannelSetting::query()->updateOrCreate(
            ['school_id' => $schoolId, 'channel' => $channel],
            $data,
        );
    }
}
