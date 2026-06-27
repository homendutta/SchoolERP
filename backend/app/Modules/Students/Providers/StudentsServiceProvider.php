<?php

declare(strict_types=1);

namespace App\Modules\Students\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Students module.
 *
 * Owns students, parents and their links, the admission pipeline, the promotion
 * lifecycle/history, and behavioural records (discipline, conduct, activities).
 * Depends on Foundation and Academic; references Staff and Finance.
 *
 * Foundation stage: structure and wiring point only — no business bindings yet.
 */
class StudentsServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Students';
}
