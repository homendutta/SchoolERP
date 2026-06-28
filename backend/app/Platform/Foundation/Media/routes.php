<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Media — API Routes (prefix: /api/v1/media)
|--------------------------------------------------------------------------
| The single upload mechanism for the whole system. Loaded by the
| AppServiceProvider (Platform infrastructure, not a business module).
*/

use App\Platform\Foundation\Media\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('media')->group(function (): void {
    Route::post('upload', [MediaController::class, 'upload'])->middleware('permission:media.upload');
    Route::get('{id}', [MediaController::class, 'show'])->middleware('permission:media.view');
    Route::get('{id}/download', [MediaController::class, 'download'])
        ->middleware('permission:media.view')
        ->name('media.download');
    Route::post('{id}/replace', [MediaController::class, 'replace'])->middleware('permission:media.upload');
    Route::delete('{id}', [MediaController::class, 'destroy'])->middleware('permission:media.delete');
});
