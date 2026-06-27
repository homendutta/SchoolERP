<?php

declare(strict_types=1);

namespace App\Modules\Staff\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Staff module.
 *
 * Owns the employee master record and the teacher-as-resource identity. Depends
 * on Foundation; referenced by Academic, Attendance, Examination, Finance,
 * Communication, and Asset for the staff/teacher identity.
 *
 * Foundation stage: structure and wiring point only — no business bindings yet.
 */
class StaffServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Staff';
}
