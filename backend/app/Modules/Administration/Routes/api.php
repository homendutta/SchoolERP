<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Administration — API Routes (prefix: /api/v1)
|--------------------------------------------------------------------------
| All routes require a Sanctum token and the relevant permission slug.
*/

use App\Modules\Administration\Http\Controllers\ExportController;
use App\Modules\Administration\Http\Controllers\FeatureFlagController;
use App\Modules\Administration\Http\Controllers\GatewaysController;
use App\Modules\Administration\Http\Controllers\ImportController;
use App\Modules\Administration\Http\Controllers\MasterDataGroupController;
use App\Modules\Administration\Http\Controllers\MasterDataTypeController;
use App\Modules\Administration\Http\Controllers\MasterDataValueController;
use App\Modules\Administration\Http\Controllers\NumberSequenceController;
use App\Modules\Administration\Http\Controllers\RolesController;
use App\Modules\Administration\Http\Controllers\SchoolSettingsController;
use App\Modules\Administration\Http\Controllers\SettingsController;
use App\Modules\Administration\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    // Identity / access (Sprint 1)
    Route::get('users', [UsersController::class, 'index'])->middleware('permission:users.view');
    Route::get('roles', [RolesController::class, 'index'])->middleware('permission:roles.view');
    Route::get('permissions', [RolesController::class, 'permissions'])->middleware('permission:roles.view');

    Route::prefix('admin')->group(function (): void {
        // School Settings
        Route::get('school', [SchoolSettingsController::class, 'show'])->middleware('permission:school.view');
        Route::put('school', [SchoolSettingsController::class, 'update'])->middleware('permission:school.update');

        // Settings Engine
        Route::get('settings', [SettingsController::class, 'index'])->middleware('permission:settings.view');
        Route::get('settings/{group}', [SettingsController::class, 'show'])->middleware('permission:settings.view');
        Route::put('settings/{group}', [SettingsController::class, 'update'])->middleware('permission:settings.update');

        // Master Data Engine — reusable CRUD for groups / types / values
        $crud = function (string $uri, string $controller): void {
            Route::post("{$uri}/bulk-delete", [$controller, 'bulkDestroy'])->middleware('permission:master_data.delete');
            Route::get($uri, [$controller, 'index'])->middleware('permission:master_data.view');
            Route::post($uri, [$controller, 'store'])->middleware('permission:master_data.create');
            Route::get("{$uri}/{id}", [$controller, 'show'])->middleware('permission:master_data.view');
            Route::put("{$uri}/{id}", [$controller, 'update'])->middleware('permission:master_data.edit');
            Route::delete("{$uri}/{id}", [$controller, 'destroy'])->middleware('permission:master_data.delete');
            Route::post("{$uri}/{id}/archive", [$controller, 'archive'])->middleware('permission:master_data.delete');
            Route::post("{$uri}/{id}/restore", [$controller, 'restore'])->middleware('permission:master_data.edit');
        };
        $crud('master-data/groups', MasterDataGroupController::class);
        $crud('master-data/types', MasterDataTypeController::class);
        $crud('master-data/values', MasterDataValueController::class);

        // Number Generator
        Route::get('number-sequences', [NumberSequenceController::class, 'index'])->middleware('permission:number_generator.view');
        Route::get('number-sequences/{id}', [NumberSequenceController::class, 'show'])->middleware('permission:number_generator.view');
        Route::put('number-sequences/{id}', [NumberSequenceController::class, 'update'])->middleware('permission:number_generator.manage');
        Route::get('number-sequences/{key}/preview', [NumberSequenceController::class, 'preview'])->middleware('permission:number_generator.view');
        Route::get('number-sequences/{key}/history', [NumberSequenceController::class, 'history'])->middleware('permission:number_generator.view');
        Route::post('number-sequences/{key}/reset', [NumberSequenceController::class, 'reset'])->middleware('permission:number_generator.reset');

        // Feature Flags
        Route::get('feature-flags', [FeatureFlagController::class, 'index'])->middleware('permission:feature_flags.view');
        Route::put('feature-flags/{key}', [FeatureFlagController::class, 'update'])->middleware('permission:feature_flags.manage');

        // Gateways
        Route::get('gateways/email', [GatewaysController::class, 'email'])->middleware('permission:gateways.view');
        Route::put('gateways/email', [GatewaysController::class, 'updateEmail'])->middleware('permission:gateways.manage');
        Route::post('gateways/email/test', [GatewaysController::class, 'testEmail'])->middleware('permission:gateways.test');
        Route::get('gateways/sms', [GatewaysController::class, 'sms'])->middleware('permission:gateways.view');
        Route::put('gateways/sms', [GatewaysController::class, 'updateSms'])->middleware('permission:gateways.manage');
        Route::post('gateways/sms/test', [GatewaysController::class, 'testSms'])->middleware('permission:gateways.test');
        Route::get('gateways/payments', [GatewaysController::class, 'payments'])->middleware('permission:gateways.view');
        Route::put('gateways/payments/{provider}', [GatewaysController::class, 'updatePayment'])->middleware('permission:gateways.manage');

        // Import / Export framework
        Route::post('import/upload', [ImportController::class, 'upload'])->middleware('permission:import.execute');
        Route::post('import/validate', [ImportController::class, 'validateRows'])->middleware('permission:import.execute');
        Route::post('import/execute', [ImportController::class, 'execute'])->middleware('permission:import.execute');
        Route::post('export', [ExportController::class, 'export'])->middleware('permission:export.execute');
    });
});
