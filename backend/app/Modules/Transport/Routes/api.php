<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Transport — API Routes (prefix: /api/v1/transport)
|--------------------------------------------------------------------------
| Vehicles (Number Generator + Media docs), routes + stops, scheduled trips,
| student assignments (route+stop, never a vehicle; capacity enforced), driver/
| attendant assignments (Staff), transport fee definitions (Finance collects),
| and maintenance schedules. Notifications go through the Communication Engine.
*/

use App\Modules\Transport\Http\Controllers\DriverController;
use App\Modules\Transport\Http\Controllers\FeeController;
use App\Modules\Transport\Http\Controllers\MaintenanceController;
use App\Modules\Transport\Http\Controllers\RouteController;
use App\Modules\Transport\Http\Controllers\StopController;
use App\Modules\Transport\Http\Controllers\StudentAssignmentController;
use App\Modules\Transport\Http\Controllers\TransportDashboardController;
use App\Modules\Transport\Http\Controllers\TripController;
use App\Modules\Transport\Http\Controllers\VehicleController;
use App\Modules\Transport\Http\Controllers\VehicleDocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('transport')->group(function (): void {
    $view = 'permission:transport.view';
    $manage = 'permission:transport.manage';
    $assign = 'permission:transport.assign';

    Route::get('dashboard', [TransportDashboardController::class, 'overview'])->middleware($view);

    $crud = function (string $name, string $controller) use ($view, $manage): void {
        Route::post("$name/bulk-delete", [$controller, 'bulkDestroy'])->middleware($manage);
        Route::get($name, [$controller, 'index'])->middleware($view);
        Route::post($name, [$controller, 'store'])->middleware($manage);
        Route::get("$name/{id}", [$controller, 'show'])->middleware($view);
        Route::put("$name/{id}", [$controller, 'update'])->middleware($manage);
        Route::delete("$name/{id}", [$controller, 'destroy'])->middleware($manage);
    };

    $crud('vehicles', VehicleController::class);
    $crud('routes', RouteController::class);
    $crud('stops', StopController::class);
    $crud('trips', TripController::class);
    $crud('drivers', DriverController::class);
    $crud('documents', VehicleDocumentController::class);
    $crud('fees', FeeController::class);
    $crud('maintenance', MaintenanceController::class);

    // Student assignments (route + stop; capacity enforced; history preserved)
    Route::get('students', [StudentAssignmentController::class, 'index'])->middleware($view);
    Route::post('students', [StudentAssignmentController::class, 'assign'])->middleware($assign);
    Route::post('students/{id}/cancel', [StudentAssignmentController::class, 'cancel'])->middleware($assign);
});
