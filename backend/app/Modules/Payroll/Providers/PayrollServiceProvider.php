<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Providers;

use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Policies\PayrollPolicy;
use App\Platform\Core\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Payroll module (Sprint 16B).
 *
 * Salary components/structures, employee salary assignments + revisions
 * (historical, immutable versions), overtime, loans/advances, arrears, statutory
 * components, and the reusable, IDEMPOTENT Payroll Engine (runs → payslips). It
 * CONSUMES HR (salary structures), Attendance and Leave (read-only) and Finance
 * (settlement recorded elsewhere) — it never edits them. Payroll runs are
 * immutable once locked; corrections require a new run. All payroll numbers come
 * from the Number Generator; payslip QR uses the Identity Platform; notifications
 * go through the Communication Engine; every action writes to the Audit Log.
 *
 * Designed so Income-Tax computation, e-filing, bank-transfer files, direct
 * salary APIs, employee self-service payroll, mobile payslips, multi-company /
 * multi-currency payroll and AI anomaly detection can be added without
 * structural change.
 */
class PayrollServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Payroll';

    protected function registerPolicies(): void
    {
        Gate::policy(PayrollRun::class, PayrollPolicy::class);
    }
}
