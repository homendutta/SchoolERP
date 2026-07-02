<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Hostel — API Routes (prefix: /api/v1/hostel)
|--------------------------------------------------------------------------
| Hostels (Number Generator code) → buildings → floors → rooms (Master Data
| room type; capacity enforced) → beds (Number Generator code). Students occupy
| beds (never rooms directly); a bed is single-occupant; history is preserved.
| Wardens are Staff; fees are collected by Finance; notifications go through the
| Communication Engine.
*/

use App\Modules\Hostel\Http\Controllers\AllocationController;
use App\Modules\Hostel\Http\Controllers\BedController;
use App\Modules\Hostel\Http\Controllers\BuildingController;
use App\Modules\Hostel\Http\Controllers\FeeController;
use App\Modules\Hostel\Http\Controllers\FloorController;
use App\Modules\Hostel\Http\Controllers\HostelController;
use App\Modules\Hostel\Http\Controllers\HostelDashboardController;
use App\Modules\Hostel\Http\Controllers\MaintenanceController;
use App\Modules\Hostel\Http\Controllers\RoomController;
use App\Modules\Hostel\Http\Controllers\TransferController;
use App\Modules\Hostel\Http\Controllers\VisitorController;
use App\Modules\Hostel\Http\Controllers\WardenController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('hostel')->group(function (): void {
    $view = 'permission:hostel.view';
    $manage = 'permission:hostel.manage';
    $allocate = 'permission:hostel.allocate';

    Route::get('dashboard', [HostelDashboardController::class, 'overview'])->middleware($view);
    Route::get('occupancy', [HostelDashboardController::class, 'occupancy'])->middleware($view);

    $crud = function (string $name, string $controller) use ($view, $manage): void {
        Route::post("$name/bulk-delete", [$controller, 'bulkDestroy'])->middleware($manage);
        Route::get($name, [$controller, 'index'])->middleware($view);
        Route::post($name, [$controller, 'store'])->middleware($manage);
        Route::get("$name/{id}", [$controller, 'show'])->middleware($view);
        Route::put("$name/{id}", [$controller, 'update'])->middleware($manage);
        Route::delete("$name/{id}", [$controller, 'destroy'])->middleware($manage);
    };

    $crud('hostels', HostelController::class);
    $crud('buildings', BuildingController::class);
    $crud('floors', FloorController::class);
    $crud('rooms', RoomController::class);
    $crud('beds', BedController::class);
    $crud('wardens', WardenController::class);
    $crud('visitors', VisitorController::class);
    $crud('maintenance', MaintenanceController::class);
    $crud('fees', FeeController::class);

    // Student allocation (bed single-occupant; history preserved)
    Route::get('allocations', [AllocationController::class, 'index'])->middleware($view);
    Route::post('allocations', [AllocationController::class, 'allocate'])->middleware($allocate);
    Route::post('allocations/{id}/checkout', [AllocationController::class, 'checkout'])->middleware($allocate);

    // Transfers (room/bed/building/hostel change; new records, full history)
    Route::get('transfers', [TransferController::class, 'index'])->middleware($view);
    Route::post('transfers', [TransferController::class, 'transfer'])->middleware($allocate);
});
