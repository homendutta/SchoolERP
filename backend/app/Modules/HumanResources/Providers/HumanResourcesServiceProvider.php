<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Providers;

use App\Modules\HumanResources\Models\EmploymentRecord;
use App\Modules\HumanResources\Policies\HrPolicy;
use App\Platform\Core\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Human Resources module (Sprint 16A).
 *
 * Manages the employee lifecycle: departments/designations (hierarchical, codes
 * from the Number Generator), employment HISTORY (never overwritten), employee
 * documents (Media references), shifts, attendance policies (consumed by the
 * Attendance module), leave types/policies/requests (Leave Engine with
 * multi-level approval + balance tracking), holidays, performance reviews,
 * training, disciplinary records and employee separation. Every action writes to
 * the Audit Log and the Staff Timeline; notifications go through the
 * Communication Engine. Payroll is intentionally excluded (Sprint 16B).
 *
 * Designed so Recruitment / ATS / Interview Scheduling / Offer Management /
 * Onboarding / Payroll / Succession Planning / Competency Framework / Employee &
 * Manager Self-Service can be added without structural change.
 */
class HumanResourcesServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'HumanResources';

    protected function registerPolicies(): void
    {
        Gate::policy(EmploymentRecord::class, HrPolicy::class);
    }
}
