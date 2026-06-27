<?php

declare(strict_types=1);

namespace App\Modules\Administration\Providers;

use App\Modules\Administration\Models\Role;
use App\Modules\Administration\Models\User;
use App\Modules\Administration\Policies\RolePolicy;
use App\Modules\Administration\Policies\UserPolicy;
use App\Modules\Administration\Support\ImporterRegistry;
use App\Platform\Core\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Administration module.
 *
 * Owns the school's foundation business data and access model:
 *   - Schools · Users · Roles · Permissions (+ user_roles, role_permissions)
 *   - Settings & System Configuration · Master Data
 *   - Number Generator & Business Number Registry
 *
 * (Infrastructure these build on — audit, media, cache, tenancy — lives in
 * Platform/Foundation.) Depended upon by most modules.
 */
class AdministrationServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Administration';

    protected function registerBindings(): void
    {
        // The Import framework's registry is a singleton other modules register into.
        $this->app->singleton(ImporterRegistry::class);
    }

    protected function registerPolicies(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
    }
}
