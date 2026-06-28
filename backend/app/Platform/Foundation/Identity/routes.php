<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Identity — API Routes (prefix: /api/v1/identity)
|--------------------------------------------------------------------------
| Platform infrastructure (not a business module). Loaded by AppServiceProvider.
| The single source of truth for QR codes and barcodes.
*/

use App\Platform\Foundation\Identity\Http\Controllers\IdentityController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('identity')->group(function (): void {
    Route::get('search', [IdentityController::class, 'search'])->middleware('permission:identity.view');
    Route::post('regenerate', [IdentityController::class, 'regenerate'])->middleware('permission:identity.manage');
    Route::get('{id}', [IdentityController::class, 'show'])->middleware('permission:identity.view');
    Route::get('{id}/qr', [IdentityController::class, 'qr'])->middleware('permission:identity.view');
    Route::get('{id}/barcode', [IdentityController::class, 'barcode'])->middleware('permission:identity.view');
    Route::post('{id}/status', [IdentityController::class, 'status'])->middleware('permission:identity.manage');
});
