<?php

declare(strict_types=1);

namespace App\Modules\Examination\Providers;

use App\Modules\Administration\Support\ImporterRegistry;
use App\Modules\Examination\Models\ExamSession;
use App\Modules\Examination\Policies\ExaminationPolicy;
use App\Modules\Examination\Support\MarksImporter;
use App\Platform\Core\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Examination module.
 *
 * Owns the full examination lifecycle — types, sessions, subject mapping (with
 * optional/elective correctness), schedules/seating/invigilation, exam
 * attendance, marks, configurable grading + ranking, result processing, report
 * cards, tabulation and promotion readiness. Reuses Academic, Timetable, Staff,
 * Students and the Identity Platform. Marks import plugs into the generic Import
 * framework.
 */
class ExaminationServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Examination';

    protected function registerBindings(): void
    {
        $this->callAfterResolving(ImporterRegistry::class, function (ImporterRegistry $registry): void {
            $registry->register($this->app->make(MarksImporter::class));
        });
    }

    protected function registerPolicies(): void
    {
        Gate::policy(ExamSession::class, ExaminationPolicy::class);
    }
}
