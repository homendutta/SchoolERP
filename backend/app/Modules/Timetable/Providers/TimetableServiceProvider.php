<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Providers;

use App\Modules\Timetable\Models\ClassTimetable;
use App\Modules\Timetable\Policies\TimetablePolicy;
use App\Platform\Core\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Timetable module.
 *
 * Owns the bell schedule (periods), configurable working days, and the master
 * class timetable with clash prevention and teacher-workload calculation.
 * Teacher and Room timetables are DERIVED from the class timetable — never
 * stored separately. Substitutions and special events are separate records and
 * never modify the master. The timetable is reusable infrastructure: other
 * modules read the schedule via TimetableScheduleService.
 *
 * Depends on Academic (class/section/subject/room) and Staff (teacher).
 */
class TimetableServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Timetable';

    protected function registerPolicies(): void
    {
        Gate::policy(ClassTimetable::class, TimetablePolicy::class);
    }
}
