<?php

declare(strict_types=1);

namespace App\Platform\Core\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

/**
 * Abstract base for every business-module service provider.
 *
 * Each module is a self-contained vertical slice. Its provider is the single
 * wiring point where the module registers:
 *   - container bindings (repository interface -> implementation),
 *   - policies (resource -> policy),
 *   - event listeners,
 *   - migrations (module-owned),
 *   - routes (a versioned API group + web group).
 *
 * The base auto-discovers the module's directory and loads its
 * Database/Migrations and Routes/{api,web}.php so concrete providers only
 * declare the module name and override the hooks they need.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /** The module's name (matches app/Modules/<name>). */
    protected string $moduleName = '';

    public function register(): void
    {
        $this->registerBindings();
    }

    public function boot(): void
    {
        $this->loadMigrations();
        $this->registerPolicies();
        $this->registerListeners();
        $this->registerRoutes();
    }

    /** Bind module interfaces to concrete implementations. */
    protected function registerBindings(): void {}

    /** Map module resources to their policies. */
    protected function registerPolicies(): void {}

    /** Subscribe module listeners to domain events. */
    protected function registerListeners(): void {}

    /** Load the module's migrations (Database/Migrations), if present. */
    protected function loadMigrations(): void
    {
        $path = $this->modulePath('Database/Migrations');
        if (is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }
    }

    /**
     * Load the module's routes:
     *   Routes/api.php -> prefix 'api/v1' (stateless; protected routes declare auth:sanctum)
     *   Routes/web.php -> 'web' middleware group
     */
    protected function registerRoutes(): void
    {
        $api = $this->modulePath('Routes/api.php');
        if (is_file($api)) {
            Route::prefix('api/v1')->group($api);
        }

        $web = $this->modulePath('Routes/web.php');
        if (is_file($web)) {
            Route::middleware('web')->group($web);
        }
    }

    /** Absolute path inside this module's directory. */
    protected function modulePath(string $relative = ''): string
    {
        $providerFile = (new ReflectionClass($this))->getFileName();
        // .../app/Modules/<Module>/Providers/<X>ServiceProvider.php -> module dir is two levels up
        $moduleDir = dirname($providerFile, 2);

        return $relative === '' ? $moduleDir : $moduleDir.DIRECTORY_SEPARATOR.$relative;
    }

    public function moduleName(): string
    {
        return $this->moduleName;
    }
}
