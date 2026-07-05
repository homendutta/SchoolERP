<?php

declare(strict_types=1);

use App\Platform\Core\Middleware\EnsurePermission;
use App\Platform\Shared\Exceptions\DomainException;
use App\Platform\Shared\Http\Responses\ApiResponse;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
|--------------------------------------------------------------------------
| Asylinx School ERP — Application Bootstrap (Laravel 12)
|--------------------------------------------------------------------------
| Engineering foundation only. Routing is wired per the modular architecture
| (Controller -> Request -> Service -> Repository -> Model). No business
| endpoints are defined at this stage.
*/

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Cross-cutting middleware aliases. The permission middleware (Platform/Core)
        // enforces server-side RBAC: ->middleware('permission:<slug>').
        $middleware->alias([
            'permission' => EnsurePermission::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Centralized scheduler (Sprint 23). One `* * * * * php artisan schedule:run`
        // cron entry drives every recurring task; each is queued/idempotent.
        $schedule->command('system:cleanup')->dailyAt('02:30')->withoutOverlapping();
        $schedule->command('queue:prune-failed --hours=336')->weekly();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Translate domain/business errors into the standard API error envelope.
        $exceptions->render(function (DomainException $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(
                    $e->getMessage(),
                    $e->status,
                    $e->errorCode,
                );
            }

            return null;
        });
    })
    ->create();
