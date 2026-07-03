<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Parent / Student / Teacher Portal — API Routes (prefix: /api/v1/portal)
|--------------------------------------------------------------------------
| Self-service portals that CONSUME the ERP. The portal owns no business logic —
| every endpoint delegates to the owning module and enforces isolation (parents →
| linked children, students → self, teachers → their responsibilities). Online
| payment reuses the Finance Payment Engine + Gateway abstraction; Finance stays
| the source of truth.
*/

use App\Modules\Portal\Http\Controllers\PortalAuthController;
use App\Modules\Portal\Http\Controllers\PortalController;
use App\Modules\Portal\Http\Controllers\PortalFeeController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal')->group(function (): void {
    // Public auth (throttled).
    Route::middleware('throttle:10,1')->post('login', [PortalAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [PortalAuthController::class, 'logout']);
        Route::get('me', [PortalAuthController::class, 'me']);
        Route::post('change-password', [PortalAuthController::class, 'changePassword']);

        Route::get('dashboard', [PortalController::class, 'dashboard']);
        Route::get('attendance', [PortalController::class, 'attendance']);
        Route::get('examinations', [PortalController::class, 'examinations']);
        Route::get('library', [PortalController::class, 'library']);
        Route::get('transport', [PortalController::class, 'transport']);
        Route::get('hostel', [PortalController::class, 'hostel']);
        Route::get('timetable', [PortalController::class, 'timetable']);
        Route::get('messages', [PortalController::class, 'messages']);
        Route::get('downloads', [PortalController::class, 'downloads']);

        Route::get('profile', [PortalController::class, 'profile']);
        Route::put('profile', [PortalController::class, 'updateProfile']);

        // Finance (parents + students only; teachers are rejected in the service).
        Route::get('fees', [PortalFeeController::class, 'fees']);
        Route::get('fees/history', [PortalFeeController::class, 'history']);
        Route::get('fees/receipt/{id}', [PortalFeeController::class, 'receipt']);
        Route::get('payment-gateways', [PortalFeeController::class, 'gateways']);
        Route::post('fees/pay', [PortalFeeController::class, 'pay']);
    });
});
