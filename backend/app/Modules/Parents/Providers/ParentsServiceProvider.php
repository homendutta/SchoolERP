<?php

declare(strict_types=1);

namespace App\Modules\Parents\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Parents module.
 *
 * Owns parent/guardian records and the parent-student relationships (including
 * primary-contact designation). References Students; depends on Administration
 * (accounts/numbering via Platform).
 *
 * Refactor stage: structure and wiring point only — no business bindings yet.
 */
class ParentsServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Parents';
}
