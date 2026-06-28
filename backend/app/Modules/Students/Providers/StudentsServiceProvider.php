<?php

declare(strict_types=1);

namespace App\Modules\Students\Providers;

use App\Modules\Administration\Support\ImporterRegistry;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Policies\StudentPolicy;
use App\Modules\Students\Support\StudentImporter;
use App\Platform\Core\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Students module.
 *
 * Maintains the student AFTER enrollment: profile, timeline, medical, documents,
 * the immutable academic history, transfers, withdrawals and the promotion
 * engine. Students are created only by Admissions or the migration importer.
 *
 * Reuses Platform services (Media, Audit, Notifications, Number Generator,
 * Import/Export) and depends on Academic for class/section/year.
 */
class StudentsServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Students';

    protected function registerBindings(): void
    {
        // Migration-mode student import plugs into the generic Import framework.
        $this->callAfterResolving(ImporterRegistry::class, function (ImporterRegistry $registry): void {
            $registry->register($this->app->make(StudentImporter::class));
        });
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Student::class, StudentPolicy::class);
    }
}
