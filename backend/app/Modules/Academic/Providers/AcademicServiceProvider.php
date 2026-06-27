<?php

declare(strict_types=1);

namespace App\Modules\Academic\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Academic module.
 *
 * Owns the academic structure:
 *   - Academic Years & Terms
 *   - Classes & Sections
 *   - Subjects, Subject Types, Subject Groups
 *   - Houses & Streams
 *
 * The structural backbone referenced by Students, Attendance, Timetable,
 * Examination, and Finance. Depends on Administration (academic-year config).
 *
 * Refactor stage: structure and wiring point only — no business bindings yet.
 */
class AcademicServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Academic';
}
