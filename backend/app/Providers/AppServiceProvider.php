<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Application-level service provider.
 *
 * Wires application-wide concerns that are not owned by any single business
 * module. Module-specific bindings live in each module's own service provider
 * (app/Modules/<Module>/Providers).
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        // Application-wide container bindings (Shared kernel services) are
        // registered here as the platform matures. No business bindings.
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        // Platform/Foundation infrastructure migrations (e.g., the media library)
        // are not owned by a business module, so they are loaded here.
        $this->loadMigrationsFrom(app_path('Platform/Foundation/Database/Migrations'));
    }
}
