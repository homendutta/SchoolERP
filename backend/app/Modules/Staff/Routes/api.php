<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Staff — API Routes (prefix: /api/v1)
|--------------------------------------------------------------------------
| Employee master for ALL staff: profile, search, qualifications, experience,
| documents, timeline, import, export and dashboard. Department/Designation are
| Master Data; employee numbers come from the Number Generator.
*/

use App\Modules\Staff\Http\Controllers\ExperienceController;
use App\Modules\Staff\Http\Controllers\QualificationController;
use App\Modules\Staff\Http\Controllers\StaffController;
use App\Modules\Staff\Http\Controllers\StaffDashboardController;
use App\Modules\Staff\Http\Controllers\StaffDocumentController;
use App\Modules\Staff\Http\Controllers\StaffExportController;
use App\Modules\Staff\Http\Controllers\StaffImportController;
use App\Modules\Staff\Http\Controllers\TimelineController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    $crud = function (string $uri, string $controller, string $perm): void {
        Route::post("{$uri}/bulk-delete", [$controller, 'bulkDestroy'])->middleware("permission:{$perm}.delete");
        Route::get($uri, [$controller, 'index'])->middleware("permission:{$perm}.view");
        Route::post($uri, [$controller, 'store'])->middleware("permission:{$perm}.create");
        Route::get("{$uri}/{id}", [$controller, 'show'])->middleware("permission:{$perm}.view");
        Route::put("{$uri}/{id}", [$controller, 'update'])->middleware("permission:{$perm}.edit");
        Route::delete("{$uri}/{id}", [$controller, 'destroy'])->middleware("permission:{$perm}.delete");
        Route::post("{$uri}/{id}/archive", [$controller, 'archive'])->middleware("permission:{$perm}.delete");
        Route::post("{$uri}/{id}/restore", [$controller, 'restore'])->middleware("permission:{$perm}.edit");
    };

    // Dashboard
    Route::get('staff/dashboard', [StaffDashboardController::class, 'overview'])->middleware('permission:staff.view');

    // Import
    Route::post('staff-import/upload', [StaffImportController::class, 'upload'])->middleware('permission:staff.import');
    Route::post('staff-import/validate', [StaffImportController::class, 'validateRows'])->middleware('permission:staff.import');
    Route::post('staff-import/execute', [StaffImportController::class, 'execute'])->middleware('permission:staff.import');

    // Export
    Route::get('staff-export', [StaffExportController::class, 'export'])->middleware('permission:staff.export');

    // Staff (created here — Staff Management owns creation)
    $crud('staff', StaffController::class, 'staff');

    // Qualifications / Experience / Documents
    $crud('staff-qualifications', QualificationController::class, 'staff');
    $crud('staff-experience', ExperienceController::class, 'staff');
    $crud('staff-documents', StaffDocumentController::class, 'staff');

    // Timeline
    Route::get('staff-timeline', [TimelineController::class, 'index'])->middleware('permission:staff.view');
    Route::post('staff-timeline', [TimelineController::class, 'store'])->middleware('permission:staff.edit');
});
