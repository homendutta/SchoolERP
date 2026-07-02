<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Library — API Routes (prefix: /api/v1/library)
|--------------------------------------------------------------------------
| Catalog (never borrowed) + physical copies (each with its own Identity) +
| circulation (borrow/return/renew/reserve, borrower resolved via Identity) +
| inventory. Library calculates fines; Finance collects; Communication notifies.
*/

use App\Modules\Library\Http\Controllers\AuthorController;
use App\Modules\Library\Http\Controllers\BookController;
use App\Modules\Library\Http\Controllers\BorrowingController;
use App\Modules\Library\Http\Controllers\CategoryController;
use App\Modules\Library\Http\Controllers\CirculationController;
use App\Modules\Library\Http\Controllers\CopyController;
use App\Modules\Library\Http\Controllers\FineRuleController;
use App\Modules\Library\Http\Controllers\InventoryController;
use App\Modules\Library\Http\Controllers\LibraryDashboardController;
use App\Modules\Library\Http\Controllers\LocationController;
use App\Modules\Library\Http\Controllers\PublisherController;
use App\Modules\Library\Http\Controllers\ReservationController;
use App\Modules\Library\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('library')->group(function (): void {
    $view = 'permission:library.view';
    $manage = 'permission:library.manage';
    $circulate = 'permission:library.circulate';
    $inventory = 'permission:library.inventory';

    Route::get('dashboard', [LibraryDashboardController::class, 'overview'])->middleware($view);

    // Settings (circulation policy)
    Route::get('settings', [SettingsController::class, 'show'])->middleware($view);
    Route::put('settings', [SettingsController::class, 'update'])->middleware($manage);

    // Reference CRUD + catalog + copies
    $crud = function (string $name, string $controller) use ($view, $manage): void {
        Route::post("$name/bulk-delete", [$controller, 'bulkDestroy'])->middleware($manage);
        Route::get($name, [$controller, 'index'])->middleware($view);
        Route::post($name, [$controller, 'store'])->middleware($manage);
        Route::get("$name/{id}", [$controller, 'show'])->middleware($view);
        Route::put("$name/{id}", [$controller, 'update'])->middleware($manage);
        Route::delete("$name/{id}", [$controller, 'destroy'])->middleware($manage);
    };

    $crud('catalog', BookController::class);
    $crud('authors', AuthorController::class);
    $crud('publishers', PublisherController::class);
    $crud('categories', CategoryController::class);
    $crud('locations', LocationController::class);
    $crud('copies', CopyController::class);
    $crud('fine-rules', FineRuleController::class);

    // Circulation (borrower resolved via Identity; copy is the borrowable unit)
    Route::get('borrowings', [BorrowingController::class, 'index'])->middleware($view);
    Route::get('borrowings/{id}', [BorrowingController::class, 'show'])->middleware($view);
    Route::post('borrow', [CirculationController::class, 'borrow'])->middleware($circulate);
    Route::post('return', [CirculationController::class, 'returnCopy'])->middleware($circulate);
    Route::post('renew', [CirculationController::class, 'renew'])->middleware($circulate);

    // Reservations
    Route::get('reservations', [ReservationController::class, 'index'])->middleware($view);
    Route::post('reservations', [CirculationController::class, 'reserve'])->middleware($circulate);
    Route::post('reservations/{id}/cancel', [ReservationController::class, 'cancel'])->middleware($circulate);

    // Inventory verification
    Route::get('inventory/report', [InventoryController::class, 'report'])->middleware($inventory);
    Route::get('inventory', [InventoryController::class, 'index'])->middleware($inventory);
    Route::post('inventory', [InventoryController::class, 'store'])->middleware($inventory);
});
