<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Certificate & Document Management — API Routes
|--------------------------------------------------------------------------
| The single source of truth for official ERP documents. Templates are versioned;
| generated documents are immutable (regeneration creates a new version). QR codes
| render dynamically (never stored) via the Identity Platform; signatures use the
| Media Platform; every generation is audited + timelined; Communication notifies.
|
|   /api/v1/documents/*             — admin (RBAC)
|   /api/v1/public/document/verify  — public verification (no login)
*/

use App\Modules\Documents\Http\Controllers\CategoryController;
use App\Modules\Documents\Http\Controllers\CertificateTypeController;
use App\Modules\Documents\Http\Controllers\DocumentDashboardController;
use App\Modules\Documents\Http\Controllers\GenerationController;
use App\Modules\Documents\Http\Controllers\PublicVerificationController;
use App\Modules\Documents\Http\Controllers\TemplateController;
use App\Modules\Documents\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

// -------------------- Public verification (no login, throttled) --------------------
Route::middleware('throttle:30,1')->post('public/document/verify', [PublicVerificationController::class, 'verify']);

// -------------------- Admin --------------------
Route::middleware('auth:sanctum')->prefix('documents')->group(function (): void {
    $view = 'permission:documents.view';
    $manage = 'permission:documents.manage';
    $generate = 'permission:documents.generate';

    Route::get('dashboard', [DocumentDashboardController::class, 'overview'])->middleware($view);

    $crud = function (string $name, string $controller) use ($view, $manage): void {
        Route::get($name, [$controller, 'index'])->middleware($view);
        Route::post($name, [$controller, 'store'])->middleware($manage);
        Route::get("$name/{id}", [$controller, 'show'])->middleware($view);
        Route::put("$name/{id}", [$controller, 'update'])->middleware($manage);
        Route::delete("$name/{id}", [$controller, 'destroy'])->middleware($manage);
    };

    $crud('categories', CategoryController::class);
    $crud('certificate-types', CertificateTypeController::class);
    $crud('templates', TemplateController::class);
    Route::post('templates/{id}/version', [TemplateController::class, 'version'])->middleware($manage);

    // Generation
    Route::post('preview', [GenerationController::class, 'preview'])->middleware($generate);
    Route::post('generate', [GenerationController::class, 'generate'])->middleware($generate);
    Route::post('bulk', [GenerationController::class, 'bulk'])->middleware($generate);
    Route::post('history/{id}/regenerate', [GenerationController::class, 'regenerate'])->middleware($generate);
    Route::get('history', [GenerationController::class, 'history'])->middleware($view);
    Route::get('history/{id}', [GenerationController::class, 'show'])->middleware($view);
    Route::get('history/{id}/qr', [VerificationController::class, 'qr'])->middleware($view);

    // Verification (admin)
    Route::post('verify', [VerificationController::class, 'verify'])->middleware($view);
});
