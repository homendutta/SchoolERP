<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Students — API Routes (prefix: /api/v1)
|--------------------------------------------------------------------------
| Student lifecycle AFTER enrollment: profile, search, timeline, medical,
| documents, academic history, transfers, withdrawals, promotion, import,
| export, dashboard and ID-card/QR preparation. Students are never created here.
*/

use App\Modules\Students\Http\Controllers\AcademicRecordController;
use App\Modules\Students\Http\Controllers\MedicalController;
use App\Modules\Students\Http\Controllers\PromotionController;
use App\Modules\Students\Http\Controllers\StudentController;
use App\Modules\Students\Http\Controllers\StudentDashboardController;
use App\Modules\Students\Http\Controllers\StudentDocumentController;
use App\Modules\Students\Http\Controllers\StudentExportController;
use App\Modules\Students\Http\Controllers\StudentGuardianController;
use App\Modules\Students\Http\Controllers\StudentImportController;
use App\Modules\Students\Http\Controllers\TimelineController;
use App\Modules\Students\Http\Controllers\TransferController;
use App\Modules\Students\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    // Dashboard
    Route::get('students/dashboard', [StudentDashboardController::class, 'overview'])->middleware('permission:students.view');

    // Import (migration mode — a non-admission creation path)
    Route::post('student-import/upload', [StudentImportController::class, 'upload'])->middleware('permission:students.import');
    Route::post('student-import/validate', [StudentImportController::class, 'validateRows'])->middleware('permission:students.import');
    Route::post('student-import/execute', [StudentImportController::class, 'execute'])->middleware('permission:students.import');

    // Export
    Route::get('student-export', [StudentExportController::class, 'export'])->middleware('permission:students.export');

    // Students (no create/delete — students originate from Admissions)
    Route::get('students', [StudentController::class, 'index'])->middleware('permission:students.view');
    Route::get('students/{id}', [StudentController::class, 'show'])->middleware('permission:students.view');
    Route::put('students/{id}', [StudentController::class, 'update'])->middleware('permission:students.edit');
    Route::post('students/{id}/archive', [StudentController::class, 'archive'])->middleware('permission:students.edit');
    Route::post('students/{id}/restore', [StudentController::class, 'restore'])->middleware('permission:students.edit');
    Route::get('students/{id}/id-card', [StudentController::class, 'idCard'])->middleware('permission:students.view');
    Route::get('students/{id}/qr', [StudentController::class, 'qr'])->middleware('permission:students.view');

    // Guardians (relationship lives on the pivot; relationship type is Master Data)
    Route::post('students/{id}/guardians', [StudentGuardianController::class, 'store'])->middleware('permission:students.edit');
    Route::put('students/{id}/guardians/{guardianId}', [StudentGuardianController::class, 'update'])->middleware('permission:students.edit');
    Route::delete('students/{id}/guardians/{guardianId}', [StudentGuardianController::class, 'destroy'])->middleware('permission:students.edit');

    // Timeline
    Route::get('student-timeline', [TimelineController::class, 'index'])->middleware('permission:students.view');
    Route::post('student-timeline', [TimelineController::class, 'store'])->middleware('permission:students.edit');

    // Medical
    Route::put('student-medical/{id}', [MedicalController::class, 'update'])->middleware('permission:students.edit');

    // Documents
    Route::get('student-documents', [StudentDocumentController::class, 'index'])->middleware('permission:students.view');
    Route::post('student-documents', [StudentDocumentController::class, 'store'])->middleware('permission:students.edit');
    Route::get('student-documents/{id}', [StudentDocumentController::class, 'show'])->middleware('permission:students.view');
    Route::put('student-documents/{id}', [StudentDocumentController::class, 'update'])->middleware('permission:students.edit');
    Route::delete('student-documents/{id}', [StudentDocumentController::class, 'destroy'])->middleware('permission:students.edit');

    // Academic records (read-only history)
    Route::get('student-academic-records', [AcademicRecordController::class, 'index'])->middleware('permission:students.view');
    Route::get('student-academic-records/{id}', [AcademicRecordController::class, 'show'])->middleware('permission:students.view');

    // Transfers
    Route::get('student-transfer', [TransferController::class, 'index'])->middleware('permission:students.view');
    Route::post('student-transfer/{id}', [TransferController::class, 'store'])->middleware('permission:students.transfer');

    // Withdrawals
    Route::get('student-withdrawal', [WithdrawalController::class, 'index'])->middleware('permission:students.view');
    Route::post('student-withdrawal/{id}', [WithdrawalController::class, 'store'])->middleware('permission:students.withdraw');

    // Promotion
    Route::post('student-promotion/{id}', [PromotionController::class, 'store'])->middleware('permission:students.promote');
});
