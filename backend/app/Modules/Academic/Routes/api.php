<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Academic — API Routes (prefix: /api/v1)
|--------------------------------------------------------------------------
| All routes require a Sanctum token and the relevant academic permission slug.
| Structural backbone: Academic Years, Terms, Calendar, Classes, Sections,
| Rooms, Subjects, Subject Groups, Teacher & Class-Teacher assignments.
*/

use App\Modules\Academic\Http\Controllers\AcademicCalendarController;
use App\Modules\Academic\Http\Controllers\AcademicYearController;
use App\Modules\Academic\Http\Controllers\CalendarEventController;
use App\Modules\Academic\Http\Controllers\ClassController;
use App\Modules\Academic\Http\Controllers\ClassTeacherController;
use App\Modules\Academic\Http\Controllers\HolidayTypeController;
use App\Modules\Academic\Http\Controllers\RoomController;
use App\Modules\Academic\Http\Controllers\SectionController;
use App\Modules\Academic\Http\Controllers\SubjectController;
use App\Modules\Academic\Http\Controllers\SubjectGroupController;
use App\Modules\Academic\Http\Controllers\TeacherSubjectAssignmentController;
use App\Modules\Academic\Http\Controllers\TermController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('academic')->group(function (): void {
    /*
     * Reusable CRUD wiring. Each entity exposes the standard 8 endpoints with a
     * permission slug per verb (view/create/edit/delete).
     */
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

    // Academic Years (+ set-current)
    Route::post('academic-years/{id}/set-current', [AcademicYearController::class, 'setCurrent'])
        ->middleware('permission:academic.years.edit');
    $crud('academic-years', AcademicYearController::class, 'academic.years');

    // Terms
    $crud('terms', TermController::class, 'academic.terms');

    // Academic Calendar — calendars, events, holiday types (reusable platform service)
    $crud('academic-calendar/calendars', AcademicCalendarController::class, 'academic.calendar');
    $crud('academic-calendar/events', CalendarEventController::class, 'academic.calendar');
    $crud('academic-calendar/holiday-types', HolidayTypeController::class, 'academic.calendar');

    // Classes & Sections & Rooms
    $crud('classes', ClassController::class, 'academic.classes');
    $crud('sections', SectionController::class, 'academic.sections');
    $crud('rooms', RoomController::class, 'academic.rooms');

    // Subjects & Subject Groups
    $crud('subjects', SubjectController::class, 'academic.subjects');
    $crud('subject-groups', SubjectGroupController::class, 'academic.subject_groups');

    // Teacher Subject Assignments
    $crud('teacher-subject-assignments', TeacherSubjectAssignmentController::class, 'academic.teacher_assignments');

    // Class Teachers (single active per AY/Class/Section + history)
    Route::get('class-teachers', [ClassTeacherController::class, 'index'])->middleware('permission:academic.class_teachers.view');
    Route::get('class-teachers/history', [ClassTeacherController::class, 'history'])->middleware('permission:academic.class_teachers.view');
    Route::post('class-teachers', [ClassTeacherController::class, 'store'])->middleware('permission:academic.class_teachers.assign');
});
