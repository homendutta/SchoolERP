<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Admissions — API Routes (prefix: /api/v1/admissions)
|--------------------------------------------------------------------------
| The complete admission workflow: Enquiries → Applications → Documents →
| Verification → Approval → Enrollment, plus Import and Dashboard. Every route
| requires a Sanctum token and the relevant admissions permission slug.
*/

use App\Modules\Admissions\Http\Controllers\AdmissionDashboardController;
use App\Modules\Admissions\Http\Controllers\AdmissionImportController;
use App\Modules\Admissions\Http\Controllers\ApplicationController;
use App\Modules\Admissions\Http\Controllers\ApprovalController;
use App\Modules\Admissions\Http\Controllers\DocumentController;
use App\Modules\Admissions\Http\Controllers\EnquiryController;
use App\Modules\Admissions\Http\Controllers\EnrollmentController;
use App\Modules\Admissions\Http\Controllers\VerificationController;
use App\Modules\Admissions\Http\Controllers\WorkflowStepController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('admissions')->group(function (): void {
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
    Route::get('dashboard', [AdmissionDashboardController::class, 'overview'])->middleware('permission:admissions.dashboard.view');

    // Enquiries
    $crud('enquiries', EnquiryController::class, 'admissions.enquiries');

    // Applications (+ submit)
    Route::post('applications/{id}/submit', [ApplicationController::class, 'submit'])->middleware('permission:admissions.applications.edit');
    $crud('applications', ApplicationController::class, 'admissions.applications');

    // Documents
    $crud('documents', DocumentController::class, 'admissions.documents');

    // Verification
    Route::post('verification/applications/{id}', [VerificationController::class, 'application'])->middleware('permission:admissions.verification.manage');
    Route::post('verification/documents/{id}', [VerificationController::class, 'document'])->middleware('permission:admissions.verification.manage');
    Route::get('verification/applications/{id}/history', [VerificationController::class, 'history'])->middleware('permission:admissions.verification.view');

    // Approval workflow — configuration + processing
    $crud('approval/workflow-steps', WorkflowStepController::class, 'admissions.approval');
    Route::post('approval/applications/{id}/start', [ApprovalController::class, 'start'])->middleware('permission:admissions.approval.manage');
    Route::post('approval/steps/{stepId}/act', [ApprovalController::class, 'act'])->middleware('permission:admissions.approval.manage');

    // Enrollment (the transactional admission → student step)
    Route::post('enroll/{id}', [EnrollmentController::class, 'enroll'])->middleware('permission:admissions.enroll.execute');

    // Import
    Route::post('import/upload', [AdmissionImportController::class, 'upload'])->middleware('permission:admissions.import.execute');
    Route::post('import/validate', [AdmissionImportController::class, 'validateRows'])->middleware('permission:admissions.import.execute');
    Route::post('import/execute', [AdmissionImportController::class, 'execute'])->middleware('permission:admissions.import.execute');
});
