<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Providers;

use App\Modules\Hostel\Models\Hostel;
use App\Modules\Hostel\Policies\HostelPolicy;
use App\Platform\Core\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Hostel module.
 *
 * Manages hostels (code from the Number Generator) → buildings → floors → rooms
 * (Master Data room type; capacity enforced) → beds (Number Generator code).
 * Students occupy BEDS (never rooms directly); a bed is single-occupant; room
 * capacity is always enforced; allocation/transfer history is never deleted.
 * Wardens are Staff; documents use the Media Platform; hostel fees are collected
 * by Finance; notifications go through the Communication Engine. Biometric
 * attendance, smart locks, RFID/QR and IoT are designed-for without any
 * structural change.
 */
class HostelServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Hostel';

    protected function registerPolicies(): void
    {
        Gate::policy(Hostel::class, HostelPolicy::class);
    }
}
