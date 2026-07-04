<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Reports & Printing Center — API Routes (prefix: /api/v1/reports)
|--------------------------------------------------------------------------
| The single reporting + printing platform. The Reporting Engine runs every
| report; the Export Engine (CSV/Excel) and Print/PDF Engine are centralized;
| large + scheduled exports use queues; scheduled delivery uses the Communication
| Engine; every export is audited. Reports never modify business data.
*/

use App\Modules\Reports\Http\Controllers\ExportsController;
use App\Modules\Reports\Http\Controllers\ReportController;
use App\Modules\Reports\Http\Controllers\ReportDashboardController;
use App\Modules\Reports\Http\Controllers\SavedController;
use App\Modules\Reports\Http\Controllers\SchedulesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('reports')->group(function (): void {
    $view = 'permission:reports.view';
    $manage = 'permission:reports.manage';

    Route::get('dashboard', [ReportDashboardController::class, 'overview'])->middleware($view);
    Route::get('catalog', [ReportController::class, 'catalog'])->middleware($view);
    Route::match(['get', 'post'], 'run', [ReportController::class, 'run'])->middleware($view);
    Route::post('export', [ReportController::class, 'export'])->middleware($view);
    Route::post('print', [ReportController::class, 'print'])->middleware($view);

    // Saved reports
    Route::get('saved', [SavedController::class, 'index'])->middleware($view);
    Route::post('saved', [SavedController::class, 'store'])->middleware($view);
    Route::get('saved/{id}', [SavedController::class, 'show'])->middleware($view);
    Route::put('saved/{id}', [SavedController::class, 'update'])->middleware($view);
    Route::delete('saved/{id}', [SavedController::class, 'destroy'])->middleware($view);

    // Scheduled reports
    Route::get('schedules', [SchedulesController::class, 'index'])->middleware($view);
    Route::post('schedules', [SchedulesController::class, 'store'])->middleware($manage);
    Route::get('schedules/{id}', [SchedulesController::class, 'show'])->middleware($view);
    Route::put('schedules/{id}', [SchedulesController::class, 'update'])->middleware($manage);
    Route::delete('schedules/{id}', [SchedulesController::class, 'destroy'])->middleware($manage);
    Route::post('schedules/{id}/run', [SchedulesController::class, 'run'])->middleware($manage);

    // Export history / queue
    Route::get('exports', [ExportsController::class, 'index'])->middleware($view);
    Route::get('exports/{id}', [ExportsController::class, 'show'])->middleware($view);
});
