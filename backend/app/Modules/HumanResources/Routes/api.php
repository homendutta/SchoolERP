<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Human Resources — API Routes (prefix: /api/v1/hr)
|--------------------------------------------------------------------------
| Departments/designations (codes from the Number Generator), employment
| history (never overwritten), employee documents (Media references), shifts,
| attendance policies (consumed by Attendance), leave types/policies/requests
| (Leave Engine), holidays, performance, training, discipline and separation.
| Notifications go through the Communication Engine. Payroll is Sprint 16B.
*/

use App\Modules\HumanResources\Http\Controllers\AttendancePolicyController;
use App\Modules\HumanResources\Http\Controllers\DepartmentController;
use App\Modules\HumanResources\Http\Controllers\DesignationController;
use App\Modules\HumanResources\Http\Controllers\DisciplinaryController;
use App\Modules\HumanResources\Http\Controllers\EmployeeDocumentController;
use App\Modules\HumanResources\Http\Controllers\EmploymentController;
use App\Modules\HumanResources\Http\Controllers\HolidayController;
use App\Modules\HumanResources\Http\Controllers\HrDashboardController;
use App\Modules\HumanResources\Http\Controllers\LeaveBalanceController;
use App\Modules\HumanResources\Http\Controllers\LeavePolicyController;
use App\Modules\HumanResources\Http\Controllers\LeaveRequestController;
use App\Modules\HumanResources\Http\Controllers\LeaveTypeController;
use App\Modules\HumanResources\Http\Controllers\PerformanceController;
use App\Modules\HumanResources\Http\Controllers\SeparationController;
use App\Modules\HumanResources\Http\Controllers\ShiftController;
use App\Modules\HumanResources\Http\Controllers\TrainingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('hr')->group(function (): void {
    $view = 'permission:hr.view';
    $manage = 'permission:hr.manage';
    $approve = 'permission:hr.approve';

    Route::get('dashboard', [HrDashboardController::class, 'overview'])->middleware($view);

    $crud = function (string $name, string $controller) use ($view, $manage): void {
        Route::post("$name/bulk-delete", [$controller, 'bulkDestroy'])->middleware($manage);
        Route::get($name, [$controller, 'index'])->middleware($view);
        Route::post($name, [$controller, 'store'])->middleware($manage);
        Route::get("$name/{id}", [$controller, 'show'])->middleware($view);
        Route::put("$name/{id}", [$controller, 'update'])->middleware($manage);
        Route::delete("$name/{id}", [$controller, 'destroy'])->middleware($manage);
    };

    // Organization structure
    $crud('departments', DepartmentController::class);
    $crud('designations', DesignationController::class);

    // Employment history + documents
    $crud('employment', EmploymentController::class);
    $crud('employee-documents', EmployeeDocumentController::class);

    // Shifts + attendance policies (consumed by the Attendance module)
    $crud('shifts', ShiftController::class);
    $crud('attendance-policies', AttendancePolicyController::class);

    // Leave configuration
    $crud('leave-types', LeaveTypeController::class);
    $crud('leave-policies', LeavePolicyController::class);

    // Leave requests — writes go through the Leave Engine
    Route::get('leave-requests', [LeaveRequestController::class, 'index'])->middleware($view);
    Route::get('leave-requests/{id}', [LeaveRequestController::class, 'show'])->middleware($view);
    Route::post('leave-requests', [LeaveRequestController::class, 'store'])->middleware($manage);
    Route::post('leave-requests/{id}/approve', [LeaveRequestController::class, 'approve'])->middleware($approve);
    Route::post('leave-requests/{id}/reject', [LeaveRequestController::class, 'reject'])->middleware($approve);
    Route::post('leave-requests/{id}/cancel', [LeaveRequestController::class, 'cancel'])->middleware($manage);
    Route::get('leave-balances', [LeaveBalanceController::class, 'index'])->middleware($view);
    Route::get('leave-balances/{id}', [LeaveBalanceController::class, 'show'])->middleware($view);

    // Holidays
    $crud('holidays', HolidayController::class);

    // Performance + training + discipline
    $crud('performance', PerformanceController::class);

    Route::get('training', [TrainingController::class, 'index'])->middleware($view);
    Route::get('training/{id}', [TrainingController::class, 'show'])->middleware($view);
    Route::post('training', [TrainingController::class, 'store'])->middleware($manage);
    Route::put('training/{id}', [TrainingController::class, 'update'])->middleware($manage);
    Route::delete('training/{id}', [TrainingController::class, 'destroy'])->middleware($manage);
    Route::post('training/{id}/participants', [TrainingController::class, 'assign'])->middleware($manage);

    $crud('discipline', DisciplinaryController::class);

    // Employee separation (never deletes the employee)
    $crud('separation', SeparationController::class);
});
