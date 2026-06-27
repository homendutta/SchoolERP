<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Timetable module.
 *
 * Owns the bell schedule (periods) and the class/teacher timetable with
 * conflict prevention, plus substitute allocation against the schedule.
 * Depends on Academic (class/subject) and Staff (teacher).
 *
 * Refactor stage: structure and wiring point only — no business bindings yet.
 */
class TimetableServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Timetable';
}
