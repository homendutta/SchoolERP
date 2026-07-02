<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Inventory & Asset — API Routes (prefix: /api/v1/inventory)
|--------------------------------------------------------------------------
| Categories/models/vendors, physical assets (each with its own Identity —
| barcode/QR; asset number from the Number Generator) and consumables (append-
| only stock movements; never given an Identity). Assignments/transfers/
| verifications/disposals are historical. Fees/accounting belong to Finance;
| notifications go through the Communication Engine.
*/

use App\Modules\Inventory\Http\Controllers\AssetController;
use App\Modules\Inventory\Http\Controllers\AssignmentController;
use App\Modules\Inventory\Http\Controllers\CategoryController;
use App\Modules\Inventory\Http\Controllers\ConsumableController;
use App\Modules\Inventory\Http\Controllers\DisposalController;
use App\Modules\Inventory\Http\Controllers\InventoryDashboardController;
use App\Modules\Inventory\Http\Controllers\MaintenanceController;
use App\Modules\Inventory\Http\Controllers\ModelController;
use App\Modules\Inventory\Http\Controllers\MovementController;
use App\Modules\Inventory\Http\Controllers\TransferController;
use App\Modules\Inventory\Http\Controllers\VendorController;
use App\Modules\Inventory\Http\Controllers\VerificationController;
use App\Modules\Inventory\Http\Controllers\WarrantyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('inventory')->group(function (): void {
    $view = 'permission:inventory.view';
    $manage = 'permission:inventory.manage';
    $assign = 'permission:inventory.assign';

    Route::get('dashboard', [InventoryDashboardController::class, 'overview'])->middleware($view);

    $crud = function (string $name, string $controller) use ($view, $manage): void {
        Route::post("$name/bulk-delete", [$controller, 'bulkDestroy'])->middleware($manage);
        Route::get($name, [$controller, 'index'])->middleware($view);
        Route::post($name, [$controller, 'store'])->middleware($manage);
        Route::get("$name/{id}", [$controller, 'show'])->middleware($view);
        Route::put("$name/{id}", [$controller, 'update'])->middleware($manage);
        Route::delete("$name/{id}", [$controller, 'destroy'])->middleware($manage);
    };

    $crud('categories', CategoryController::class);
    $crud('models', ModelController::class);
    $crud('vendors', VendorController::class);
    $crud('assets', AssetController::class);
    $crud('consumables', ConsumableController::class);
    $crud('warranties', WarrantyController::class);

    // Asset lifecycle (state machine) — current state + full history through the API
    Route::get('assets/{id}/lifecycle', [AssetController::class, 'lifecycle'])->middleware($view);
    Route::post('assets/{id}/lifecycle', [AssetController::class, 'transition'])->middleware($manage);

    // Maintenance — consumes the reusable Platform Maintenance Engine (no bulk delete)
    Route::get('maintenance', [MaintenanceController::class, 'index'])->middleware($view);
    Route::post('maintenance', [MaintenanceController::class, 'store'])->middleware($manage);
    Route::get('maintenance/{id}', [MaintenanceController::class, 'show'])->middleware($view);
    Route::put('maintenance/{id}', [MaintenanceController::class, 'update'])->middleware($manage);
    Route::delete('maintenance/{id}', [MaintenanceController::class, 'destroy'])->middleware($manage);

    // Stock movements (append-only)
    Route::get('movements', [MovementController::class, 'index'])->middleware($view);
    Route::post('movements', [MovementController::class, 'store'])->middleware($manage);

    // Assignments (historical) + returns
    Route::get('assignments', [AssignmentController::class, 'index'])->middleware($view);
    Route::post('assignments', [AssignmentController::class, 'assign'])->middleware($assign);
    Route::post('assignments/{id}/return', [AssignmentController::class, 'returnAsset'])->middleware($assign);

    // Transfers (new records, full history)
    Route::get('transfers', [TransferController::class, 'index'])->middleware($view);
    Route::post('transfers', [TransferController::class, 'transfer'])->middleware($assign);

    // Physical verification
    Route::get('verifications/report', [VerificationController::class, 'report'])->middleware($view);
    Route::get('verifications', [VerificationController::class, 'index'])->middleware($view);
    Route::post('verifications', [VerificationController::class, 'store'])->middleware($manage);

    // Disposal
    Route::get('disposals', [DisposalController::class, 'index'])->middleware($view);
    Route::post('disposals', [DisposalController::class, 'store'])->middleware($manage);
});
