<?php

declare(strict_types=1);

namespace App\Modules\Academic\Providers;

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Subject;
use App\Modules\Academic\Policies\AcademicYearPolicy;
use App\Modules\Academic\Policies\ClassPolicy;
use App\Modules\Academic\Policies\SubjectPolicy;
use App\Platform\Core\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Academic module.
 *
 * Owns the academic structure:
 *   - Academic Years & Terms
 *   - Academic Calendar (reusable platform calendar service)
 *   - Classes & Sections & Rooms
 *   - Subjects, Subject Groups
 *   - Teacher Subject Assignments & Class Teachers
 *
 * The structural backbone referenced by Students, Attendance, Timetable,
 * Examination, and Finance. Depends on Administration (master data, schools).
 */
class AcademicServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Academic';

    protected function registerPolicies(): void
    {
        Gate::policy(AcademicYear::class, AcademicYearPolicy::class);
        Gate::policy(SchoolClass::class, ClassPolicy::class);
        Gate::policy(Subject::class, SubjectPolicy::class);
    }
}
