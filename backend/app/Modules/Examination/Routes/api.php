<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Examination — API Routes (prefix: /api/v1/examinations)
|--------------------------------------------------------------------------
| The full examination lifecycle: types, sessions, subject mapping (with
| optional/elective handling), schedules, seating, exam attendance, marks,
| grades, result processing, report cards, tabulation and promotion readiness.
| Reuses Academic, Timetable, Staff, Student and Identity.
*/

use App\Modules\Examination\Http\Controllers\ExamAttendanceController;
use App\Modules\Examination\Http\Controllers\ExamComponentController;
use App\Modules\Examination\Http\Controllers\ExamDashboardController;
use App\Modules\Examination\Http\Controllers\ExamGradeController;
use App\Modules\Examination\Http\Controllers\ExamScheduleController;
use App\Modules\Examination\Http\Controllers\ExamSessionController;
use App\Modules\Examination\Http\Controllers\ExamSubjectController;
use App\Modules\Examination\Http\Controllers\ExamTypeController;
use App\Modules\Examination\Http\Controllers\InvigilatorController;
use App\Modules\Examination\Http\Controllers\MarksController;
use App\Modules\Examination\Http\Controllers\MarksImportController;
use App\Modules\Examination\Http\Controllers\PromotionReadinessController;
use App\Modules\Examination\Http\Controllers\ReportCardController;
use App\Modules\Examination\Http\Controllers\ReportCardTemplateController;
use App\Modules\Examination\Http\Controllers\ResultController;
use App\Modules\Examination\Http\Controllers\SeatingController;
use App\Modules\Examination\Http\Controllers\TabulationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('examinations')->group(function (): void {
    $view = 'permission:examinations.view';
    $manage = 'permission:examinations.manage';
    $marks = 'permission:examinations.marks';
    $publish = 'permission:examinations.publish';

    // Dashboard
    Route::get('dashboard', [ExamDashboardController::class, 'overview'])->middleware($view);

    // Helper for a simple CRUD resource (bulk-delete + index/show/store/update/destroy).
    $crud = function (string $base, string $controller) use ($view, $manage): void {
        Route::post("{$base}/bulk-delete", [$controller, 'bulkDestroy'])->middleware($manage);
        Route::get($base, [$controller, 'index'])->middleware($view);
        Route::post($base, [$controller, 'store'])->middleware($manage);
        Route::get("{$base}/{id}", [$controller, 'show'])->middleware($view);
        Route::put("{$base}/{id}", [$controller, 'update'])->middleware($manage);
        Route::delete("{$base}/{id}", [$controller, 'destroy'])->middleware($manage);
    };

    $crud('types', ExamTypeController::class);
    $crud('components', ExamComponentController::class);
    $crud('grades', ExamGradeController::class);
    $crud('report-card-templates', ReportCardTemplateController::class);

    // Sessions + lifecycle actions
    Route::post('sessions/{id}/assign-subjects', [ExamSessionController::class, 'assignSubjects'])->middleware($manage);
    Route::post('sessions/{id}/process', [ExamSessionController::class, 'process'])->middleware($marks);
    Route::post('sessions/{id}/publish', [ExamSessionController::class, 'publish'])->middleware($publish);
    $crud('sessions', ExamSessionController::class);

    // Subject mapping (+ per-student elective assignment)
    Route::get('subjects/{id}/students', [ExamSubjectController::class, 'students'])->middleware($view);
    Route::post('subjects/{id}/assign-student', [ExamSubjectController::class, 'assignStudent'])->middleware($manage);
    Route::post('subjects/{id}/unassign-student', [ExamSubjectController::class, 'unassignStudent'])->middleware($manage);
    Route::post('subjects/bulk-delete', [ExamSubjectController::class, 'bulkDestroy'])->middleware($manage);
    Route::get('subjects', [ExamSubjectController::class, 'index'])->middleware($view);
    Route::post('subjects', [ExamSubjectController::class, 'store'])->middleware($manage);
    Route::put('subjects/{id}', [ExamSubjectController::class, 'update'])->middleware($manage);
    Route::delete('subjects/{id}', [ExamSubjectController::class, 'destroy'])->middleware($manage);

    // Schedule (clash detection inside the service)
    $crud('schedules', ExamScheduleController::class);

    // Invigilators + seating
    Route::get('invigilators', [InvigilatorController::class, 'index'])->middleware($view);
    Route::post('invigilators', [InvigilatorController::class, 'store'])->middleware($manage);
    Route::put('invigilators/{id}', [InvigilatorController::class, 'update'])->middleware($manage);
    Route::delete('invigilators/{id}', [InvigilatorController::class, 'destroy'])->middleware($manage);

    Route::get('seating', [SeatingController::class, 'index'])->middleware($view);
    Route::post('seating', [SeatingController::class, 'store'])->middleware($manage);
    Route::put('seating/{id}', [SeatingController::class, 'update'])->middleware($manage);
    Route::delete('seating/{id}', [SeatingController::class, 'destroy'])->middleware($manage);

    // Exam attendance (separate from daily)
    Route::get('attendance', [ExamAttendanceController::class, 'index'])->middleware($view);
    Route::post('attendance', [ExamAttendanceController::class, 'mark'])->middleware($marks);

    // Marks (manual + import)
    Route::get('marks', [MarksController::class, 'index'])->middleware($view);
    Route::post('marks', [MarksController::class, 'store'])->middleware($marks);
    Route::post('marks/import/upload', [MarksImportController::class, 'upload'])->middleware($marks);
    Route::post('marks/import/validate', [MarksImportController::class, 'validateRows'])->middleware($marks);
    Route::post('marks/import/execute', [MarksImportController::class, 'execute'])->middleware($marks);

    // Results + report cards + tabulation + promotion readiness
    Route::get('results', [ResultController::class, 'index'])->middleware($view);
    Route::get('report-cards', [ReportCardController::class, 'show'])->middleware($view);
    Route::get('tabulation', [TabulationController::class, 'index'])->middleware($view);
    Route::get('promotion-readiness', [PromotionReadinessController::class, 'index'])->middleware($view);
});
