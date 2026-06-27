<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Authentication — API Routes (prefix: /api/v1)
|--------------------------------------------------------------------------
*/

use App\Modules\Authentication\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
});
