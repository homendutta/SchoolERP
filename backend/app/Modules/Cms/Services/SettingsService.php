<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Models\Setting;
use App\Platform\Foundation\Audit\ActivityLogger;

/** Website settings are a per-school singleton. */
class SettingsService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function forSchool(int $schoolId): Setting
    {
        return Setting::query()->firstOrCreate(['school_id' => $schoolId]);
    }

    /** @param array<string, mixed> $data */
    public function update(int $schoolId, array $data): Setting
    {
        $setting = $this->forSchool($schoolId);
        $setting->fill($data)->save();

        $this->activity->record('cms.settings_updated', 'Website settings updated', $setting, [], $schoolId, 'cms');

        return $setting->refresh();
    }
}
