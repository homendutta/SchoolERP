<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Providers;

use App\Modules\Administration\Support\ImporterRegistry;
use App\Modules\Admissions\Models\AdmissionApplication;
use App\Modules\Admissions\Policies\ApplicationPolicy;
use App\Modules\Admissions\Support\AdmissionImporter;
use App\Platform\Core\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Admissions module.
 *
 * Owns the complete applicant pipeline up to enrollment:
 *   - Admission Enquiries
 *   - Admission Applications (independent of Student records)
 *   - Document management + Verification (with history)
 *   - Configurable approval workflow
 *   - Enrollment — the ONLY path (besides migration import) that creates a
 *     Student, transactionally creating Guardian/Student/Academic Record/users.
 *
 * Depends on Academic (year/class/section), Administration (numbering, master
 * data, settings), and Platform/Foundation (audit, notifications, media).
 */
class AdmissionsServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Admissions';

    protected function registerBindings(): void
    {
        // Plug the Admission importer into the generic Import framework.
        $this->callAfterResolving(ImporterRegistry::class, function (ImporterRegistry $registry): void {
            $registry->register($this->app->make(AdmissionImporter::class));
        });
    }

    protected function registerPolicies(): void
    {
        Gate::policy(AdmissionApplication::class, ApplicationPolicy::class);
    }
}
