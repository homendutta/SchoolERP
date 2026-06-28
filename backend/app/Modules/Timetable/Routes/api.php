<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Timetable — API Routes (prefix: /api/v1/timetable)
|--------------------------------------------------------------------------
| The academic schedule: configurable periods + working days, the master class
| timetable with clash detection, derived teacher/room timetables, templates,
| substitutions and special-event overrides. Teacher and Room timetables are
| derived from class_timetables — never stored separately.
*/

use App\Modules\Timetable\Http\Controllers\ClassTimetableController;
use App\Modules\Timetable\Http\Controllers\PeriodController;
use App\Modules\Timetable\Http\Controllers\RoomTimetableController;
use App\Modules\Timetable\Http\Controllers\SpecialEventController;
use App\Modules\Timetable\Http\Controllers\SubstitutionController;
use App\Modules\Timetable\Http\Controllers\TeacherTimetableController;
use App\Modules\Timetable\Http\Controllers\TemplateController;
use App\Modules\Timetable\Http\Controllers\TimetableDashboardController;
use App\Modules\Timetable\Http\Controllers\WorkingDayController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('timetable')->group(function (): void {
    // Dashboard
    Route::get('dashboard', [TimetableDashboardController::class, 'overview'])->middleware('permission:timetable.view');

    // Periods (configurable bell schedule)
    Route::post('periods/bulk-delete', [PeriodController::class, 'bulkDestroy'])->middleware('permission:timetable.manage');
    Route::get('periods', [PeriodController::class, 'index'])->middleware('permission:timetable.view');
    Route::post('periods', [PeriodController::class, 'store'])->middleware('permission:timetable.manage');
    Route::get('periods/{id}', [PeriodController::class, 'show'])->middleware('permission:timetable.view');
    Route::put('periods/{id}', [PeriodController::class, 'update'])->middleware('permission:timetable.manage');
    Route::delete('periods/{id}', [PeriodController::class, 'destroy'])->middleware('permission:timetable.manage');

    // Working days (configurable per school)
    Route::get('working-days', [WorkingDayController::class, 'index'])->middleware('permission:timetable.view');
    Route::post('working-days/sync', [WorkingDayController::class, 'sync'])->middleware('permission:timetable.manage');

    // Templates (Summer / Winter / Exam) + copy between academic years
    Route::post('templates/copy', [TemplateController::class, 'copy'])->middleware('permission:timetable.copy');
    Route::post('templates/bulk-delete', [TemplateController::class, 'bulkDestroy'])->middleware('permission:timetable.manage');
    Route::get('templates', [TemplateController::class, 'index'])->middleware('permission:timetable.view');
    Route::post('templates', [TemplateController::class, 'store'])->middleware('permission:timetable.manage');
    Route::get('templates/{id}', [TemplateController::class, 'show'])->middleware('permission:timetable.view');
    Route::put('templates/{id}', [TemplateController::class, 'update'])->middleware('permission:timetable.manage');
    Route::delete('templates/{id}', [TemplateController::class, 'destroy'])->middleware('permission:timetable.manage');

    // Master class timetable (writes run clash detection)
    Route::get('classes/grid', [ClassTimetableController::class, 'grid'])->middleware('permission:timetable.view');
    Route::get('classes', [ClassTimetableController::class, 'index'])->middleware('permission:timetable.view');
    Route::post('classes', [ClassTimetableController::class, 'store'])->middleware('permission:timetable.manage');
    Route::get('classes/{id}', [ClassTimetableController::class, 'show'])->middleware('permission:timetable.view');
    Route::put('classes/{id}', [ClassTimetableController::class, 'update'])->middleware('permission:timetable.manage');
    Route::delete('classes/{id}', [ClassTimetableController::class, 'destroy'])->middleware('permission:timetable.manage');

    // Teacher timetable + workload (derived)
    Route::get('teachers', [TeacherTimetableController::class, 'index'])->middleware('permission:timetable.view');
    Route::get('teachers/{teacherId}', [TeacherTimetableController::class, 'show'])->middleware('permission:timetable.view');

    // Room timetable (derived)
    Route::get('rooms/{roomId}', [RoomTimetableController::class, 'show'])->middleware('permission:timetable.view');

    // Substitutions (separate records — never modify the master)
    Route::post('substitutions/bulk-delete', [SubstitutionController::class, 'bulkDestroy'])->middleware('permission:timetable.substitute');
    Route::get('substitutions', [SubstitutionController::class, 'index'])->middleware('permission:timetable.view');
    Route::post('substitutions', [SubstitutionController::class, 'store'])->middleware('permission:timetable.substitute');
    Route::get('substitutions/{id}', [SubstitutionController::class, 'show'])->middleware('permission:timetable.view');
    Route::put('substitutions/{id}', [SubstitutionController::class, 'update'])->middleware('permission:timetable.substitute');
    Route::delete('substitutions/{id}', [SubstitutionController::class, 'destroy'])->middleware('permission:timetable.substitute');

    // Special events (overrides — stored separately)
    Route::post('special-events/bulk-delete', [SpecialEventController::class, 'bulkDestroy'])->middleware('permission:timetable.manage');
    Route::get('special-events', [SpecialEventController::class, 'index'])->middleware('permission:timetable.view');
    Route::post('special-events', [SpecialEventController::class, 'store'])->middleware('permission:timetable.manage');
    Route::get('special-events/{id}', [SpecialEventController::class, 'show'])->middleware('permission:timetable.view');
    Route::put('special-events/{id}', [SpecialEventController::class, 'update'])->middleware('permission:timetable.manage');
    Route::delete('special-events/{id}', [SpecialEventController::class, 'destroy'])->middleware('permission:timetable.manage');
});
