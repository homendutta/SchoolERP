<?php

declare(strict_types=1);

namespace App\Modules\Assets\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Assets module.
 *
 * Owns the fixed-asset register and asset maintenance history. References Staff
 * (assignee) and Platform (numbering, media, audit).
 *
 * Refactor stage: structure and wiring point only — no business bindings yet.
 */
class AssetsServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Assets';
}
