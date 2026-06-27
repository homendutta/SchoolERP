<?php

declare(strict_types=1);

namespace App\Modules\Reports\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Reports module.
 *
 * A cross-cutting read model: finance, academic, attendance, admissions, roster,
 * and audit reporting. Owns no source entities — it reads across modules and
 * produces printable/exportable outputs, honouring role and data scope.
 *
 * Refactor stage: structure and wiring point only — no business bindings yet.
 */
class ReportsServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Reports';
}
