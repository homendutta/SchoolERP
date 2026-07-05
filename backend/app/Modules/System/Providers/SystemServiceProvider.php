<?php

declare(strict_types=1);

namespace App\Modules\System\Providers;

use App\Modules\System\Console\SystemCleanupCommand;
use App\Modules\System\Console\SystemDoctorCommand;
use App\Modules\System\Services\CachePlatform;
use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * System / Operations module (Sprint 23 — Production Hardening & Enterprise
 * Readiness). No new business functionality — it adds the operational spine:
 * centralized cache platform (grouped invalidation), health checks + overall
 * score, diagnostics, config/environment validation, the production dashboard,
 * backup manifests + verification, failed-job monitoring, a unified log reader,
 * the `system:doctor`/`system:cleanup` commands and the central scheduler.
 */
class SystemServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'System';

    protected function registerBindings(): void
    {
        // Shared, app-wide cache platform.
        $this->app->singleton(CachePlatform::class);

        // Operational console commands (system:doctor, system:cleanup).
        $this->commands([
            SystemDoctorCommand::class,
            SystemCleanupCommand::class,
        ]);
    }
}
