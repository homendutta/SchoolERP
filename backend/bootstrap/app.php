<?php

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
            'permission' => App\Platform\Core\Middleware\EnsurePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Translate domain/business errors into the standard API error envelope.
        $exceptions->render(function (App\Platform\Shared\Exceptions\DomainException $e, $request) {
            if ($request->expectsJson()) {
                return App\Platform\Shared\Http\Responses\ApiResponse::error(
                    $e->getMessage(),
                    $e->status,
                    $e->errorCode,
                );
            }

            return null;
        });
    })
    ->create();
