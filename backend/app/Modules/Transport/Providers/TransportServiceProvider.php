<?php

declare(strict_types=1);

namespace App\Modules\Transport\Providers;

use App\Modules\Transport\Models\Vehicle;
use App\Modules\Transport\Policies\TransportPolicy;
use App\Platform\Core\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Transport module.
 *
 * Manages vehicles (number from the Number Generator; photo/documents via the
 * Media Platform), routes + ordered stops, scheduled trips, and student
 * assignments to route+stop (never directly to a vehicle — the vehicle is
 * determined through the trip). Drivers/attendants come from Staff Management;
 * transport fees are defined here but collected by Finance; capacity is always
 * enforced; notifications go through the Communication Engine. History is never
 * deleted. GPS/RFID/live-tracking are designed-for (lat/long + boarding hooks)
 * without any structural change.
 */
class TransportServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Transport';

    protected function registerPolicies(): void
    {
        Gate::policy(Vehicle::class, TransportPolicy::class);
    }
}
