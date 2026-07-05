<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| System / Operations — API Routes (Sprint 23)
|--------------------------------------------------------------------------
| Production readiness: health, diagnostics, config validation, the production
| dashboard, backup manifests + verification, failed-job monitoring and a unified
| log reader. No business data is owned here.
|
|   GET /api/v1/health          — public liveness probe (throttled, no auth)
|   /api/v1/system/*            — operator surface (auth + RBAC)
*/

use App\Modules\System\Http\Controllers\BackupController;
use App\Modules\System\Http\Controllers\FailedJobsController;
use App\Modules\System\Http\Controllers\PublicHealthController;
use App\Modules\System\Http\Controllers\SystemController;
use Illuminate\Support\Facades\Route;

// Public liveness/readiness probe.
Route::middleware('throttle:60,1')->get('health', [PublicHealthController::class, 'ping']);

Route::middleware('auth:sanctum')->prefix('system')->group(function (): void {
    $view = 'permission:system.view';
    $manage = 'permission:system.manage';

    Route::get('dashboard', [SystemController::class, 'dashboard'])->middleware($view);
    Route::get('health', [SystemController::class, 'health'])->middleware($view);
    Route::get('diagnostics', [SystemController::class, 'diagnostics'])->middleware($view);
    Route::get('config', [SystemController::class, 'config'])->middleware($view);
    Route::get('logs', [SystemController::class, 'logs'])->middleware($view);

    Route::get('backups', [BackupController::class, 'index'])->middleware($view);
    Route::post('backups', [BackupController::class, 'store'])->middleware($manage);
    Route::get('backups/{id}', [BackupController::class, 'show'])->middleware($view);
    Route::post('backups/{id}/verify', [BackupController::class, 'verify'])->middleware($manage);

    Route::get('failed-jobs', [FailedJobsController::class, 'index'])->middleware($view);
    Route::post('failed-jobs/{id}/retry', [FailedJobsController::class, 'retry'])->middleware($manage);
    Route::delete('failed-jobs/{id}', [FailedJobsController::class, 'forget'])->middleware($manage);
});
