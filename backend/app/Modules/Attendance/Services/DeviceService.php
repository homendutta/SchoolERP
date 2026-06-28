<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Models\BiometricDevice;
use App\Platform\Shared\Services\BaseCrudService;

class DeviceService extends BaseCrudService
{
    protected function model(): string
    {
        return BiometricDevice::class;
    }

    protected function searchable(): array
    {
        return ['name', 'device_identifier', 'location'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'status', 'device_type'];
    }

    protected function sortable(): array
    {
        return ['id', 'name', 'last_sync_at', 'created_at'];
    }
}
