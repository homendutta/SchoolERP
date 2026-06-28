<?php

declare(strict_types=1);

namespace App\Modules\Staff\Providers;

use App\Modules\Administration\Support\ImporterRegistry;
use App\Modules\Staff\Models\Staff;
use App\Modules\Staff\Policies\StaffPolicy;
use App\Modules\Staff\Support\StaffImporter;
use App\Platform\Core\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Staff module.
 *
 * Owns the employee master record for ALL staff (not only teachers): profile,
 * employment, qualifications, experience, documents, timeline, import/export and
 * dashboard. Staff are created only here; employee numbers come from the Number
 * Generator and department/designation from Master Data.
 *
 * Reuses Platform services (Media, Audit, Number Generator, Import/Export).
 */
class StaffServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Staff';

    protected function registerBindings(): void
    {
        // Staff import plugs into the generic Import framework.
        $this->callAfterResolving(ImporterRegistry::class, function (ImporterRegistry $registry): void {
            $registry->register($this->app->make(StaffImporter::class));
        });
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Staff::class, StaffPolicy::class);
    }
}
