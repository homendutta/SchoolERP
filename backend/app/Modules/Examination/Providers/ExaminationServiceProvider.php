<?php

declare(strict_types=1);

namespace App\Modules\Examination\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Examination module.
 *
 * Owns exams, marks, grading/ranking, the publish-lock, hall tickets, and
 * results/marksheets. Depends on Academic (class/subject/assignment), Students,
 * and Foundation.
 *
 * Foundation stage: structure and wiring point only — no business bindings yet.
 */
class ExaminationServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Examination';
}
