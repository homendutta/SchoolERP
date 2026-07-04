<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Integrations Platform — API Routes
|--------------------------------------------------------------------------
| The single gateway to third-party systems. Modules never call providers
| directly — they resolve a provider by category through the platform. Provider
| credentials are encrypted; every request/failure is logged; webhooks verify
| signatures + retry on the queue; events are immutable; config changes are
| audited + timelined.
|
|   /api/v1/integrations/*                       — admin (RBAC)
|   /api/v1/public/integrations/webhooks/{id}    — incoming webhook (signature-verified)
*/

use App\Modules\Integrations\Http\Controllers\CategoryController;
use App\Modules\Integrations\Http\Controllers\IncomingWebhookController;
use App\Modules\Integrations\Http\Controllers\MonitoringController;
use App\Modules\Integrations\Http\Controllers\ProviderController;
use App\Modules\Integrations\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Incoming webhooks (public; signature-verified, throttled).
Route::middleware('throttle:60,1')->post('public/integrations/webhooks/{id}', [IncomingWebhookController::class, 'receive']);

Route::middleware('auth:sanctum')->prefix('integrations')->group(function (): void {
    $view = 'permission:integrations.view';
    $manage = 'permission:integrations.manage';

    Route::get('dashboard', [MonitoringController::class, 'dashboard'])->middleware($view);
    Route::get('adapters', [MonitoringController::class, 'adapters'])->middleware($view);
    Route::get('events', [MonitoringController::class, 'events'])->middleware($view);
    Route::get('logs', [MonitoringController::class, 'logs'])->middleware($view);

    $crud = function (string $name, string $controller) use ($view, $manage): void {
        Route::get($name, [$controller, 'index'])->middleware($view);
        Route::post($name, [$controller, 'store'])->middleware($manage);
        Route::get("$name/{id}", [$controller, 'show'])->middleware($view);
        Route::put("$name/{id}", [$controller, 'update'])->middleware($manage);
        Route::delete("$name/{id}", [$controller, 'destroy'])->middleware($manage);
    };

    $crud('categories', CategoryController::class);
    $crud('providers', ProviderController::class);
    $crud('webhooks', WebhookController::class);

    Route::get('providers/{id}/health', [ProviderController::class, 'health'])->middleware($view);
    Route::post('providers/{id}/test', [ProviderController::class, 'test'])->middleware($manage);
});
