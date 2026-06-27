<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Attendance module.
 *
 * Owns attendance records (daily and subject/period-wise) and their lock state.
 * Depends on Academic (class/subject/period/calendar), Students, Staff, and
 * Foundation (authorization/audit).
 *
 * Foundation stage: structure and wiring point only — no business bindings yet.
 */
class AttendanceServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Attendance';
}
