<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Payroll — API Routes (prefix: /api/v1/payroll)
|--------------------------------------------------------------------------
| Salary components/structures, employee salary assignments + revisions
| (historical), overtime, loans/advances, arrears, statutory components, and the
| Payroll Engine (idempotent runs → payslips). Payroll CONSUMES HR, Attendance,
| Leave and Finance and never edits them. Numbers use the Number Generator;
| payslip QR uses the Identity Platform; notifications go through Communication.
*/

use App\Modules\Payroll\Http\Controllers\ArrearController;
use App\Modules\Payroll\Http\Controllers\ComponentController;
use App\Modules\Payroll\Http\Controllers\LoanController;
use App\Modules\Payroll\Http\Controllers\OvertimeController;
use App\Modules\Payroll\Http\Controllers\PayrollDashboardController;
use App\Modules\Payroll\Http\Controllers\PayslipController;
use App\Modules\Payroll\Http\Controllers\RunController;
use App\Modules\Payroll\Http\Controllers\SalaryAssignmentController;
use App\Modules\Payroll\Http\Controllers\SalaryRevisionController;
use App\Modules\Payroll\Http\Controllers\StatutoryController;
use App\Modules\Payroll\Http\Controllers\StructureController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('payroll')->group(function (): void {
    $view = 'permission:payroll.view';
    $manage = 'permission:payroll.manage';
    $process = 'permission:payroll.process';

    Route::get('dashboard', [PayrollDashboardController::class, 'overview'])->middleware($view);

    $crud = function (string $name, string $controller) use ($view, $manage): void {
        Route::post("$name/bulk-delete", [$controller, 'bulkDestroy'])->middleware($manage);
        Route::get($name, [$controller, 'index'])->middleware($view);
        Route::post($name, [$controller, 'store'])->middleware($manage);
        Route::get("$name/{id}", [$controller, 'show'])->middleware($view);
        Route::put("$name/{id}", [$controller, 'update'])->middleware($manage);
        Route::delete("$name/{id}", [$controller, 'destroy'])->middleware($manage);
    };

    // Configuration
    $crud('components', ComponentController::class);
    $crud('structures', StructureController::class);
    $crud('statutory', StatutoryController::class);

    // Employee salary (historical) + revisions
    $crud('assignments', SalaryAssignmentController::class);
    $crud('revisions', SalaryRevisionController::class);

    // Overtime + arrears
    $crud('overtime', OvertimeController::class);
    $crud('arrears', ArrearController::class);

    // Loans / advances (Finance owns the cash movement)
    Route::get('loans', [LoanController::class, 'index'])->middleware($view);
    Route::post('loans', [LoanController::class, 'store'])->middleware($manage);
    Route::get('loans/{id}', [LoanController::class, 'show'])->middleware($view);
    Route::put('loans/{id}', [LoanController::class, 'update'])->middleware($manage);
    Route::delete('loans/{id}', [LoanController::class, 'destroy'])->middleware($manage);
    Route::post('loans/{id}/approve', [LoanController::class, 'approve'])->middleware($manage);

    // Payroll runs — the engine processes (idempotent) and locks
    Route::get('runs', [RunController::class, 'index'])->middleware($view);
    Route::post('runs', [RunController::class, 'store'])->middleware($manage);
    Route::get('runs/{id}', [RunController::class, 'show'])->middleware($view);
    Route::put('runs/{id}', [RunController::class, 'update'])->middleware($manage);
    Route::delete('runs/{id}', [RunController::class, 'destroy'])->middleware($manage);
    Route::post('runs/{id}/process', [RunController::class, 'process'])->middleware($process);
    Route::post('runs/{id}/lock', [RunController::class, 'lock'])->middleware($process);

    // Payslips (structured data; settlement status)
    Route::get('payslips', [PayslipController::class, 'index'])->middleware($view);
    Route::get('payslips/{id}', [PayslipController::class, 'show'])->middleware($view);
    Route::post('payslips/{id}/settle', [PayslipController::class, 'settle'])->middleware($manage);
});
