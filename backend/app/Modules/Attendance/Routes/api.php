<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Attendance — API Routes (prefix: /api/v1/attendance)
|--------------------------------------------------------------------------
| One engine, three sources (manual / import / biometric). People are matched
| by Platform Identity; devices are vendor-independent via the connector layer.
*/

use App\Modules\Attendance\Http\Controllers\AttendanceDashboardController;
use App\Modules\Attendance\Http\Controllers\AttendanceImportController;
use App\Modules\Attendance\Http\Controllers\BiometricController;
use App\Modules\Attendance\Http\Controllers\DeviceController;
use App\Modules\Attendance\Http\Controllers\ManualAttendanceController;
use App\Modules\Attendance\Http\Controllers\StaffAttendanceController;
use App\Modules\Attendance\Http\Controllers\StudentAttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('attendance')->group(function (): void {
    // Dashboard
    Route::get('dashboard', [AttendanceDashboardController::class, 'overview'])->middleware('permission:attendance.view');

    // Read (unified engine, scoped by owner type)
    Route::get('student', [StudentAttendanceController::class, 'index'])->middleware('permission:attendance.view');
    Route::get('staff', [StaffAttendanceController::class, 'index'])->middleware('permission:attendance.view');

    // Manual marking + correction
    Route::post('manual', [ManualAttendanceController::class, 'store'])->middleware('permission:attendance.mark');
    Route::put('manual/{id}', [ManualAttendanceController::class, 'correct'])->middleware('permission:attendance.correct');

    // Import
    Route::post('import/upload', [AttendanceImportController::class, 'upload'])->middleware('permission:attendance.import');
    Route::post('import/validate', [AttendanceImportController::class, 'validateRows'])->middleware('permission:attendance.import');
    Route::post('import/execute', [AttendanceImportController::class, 'execute'])->middleware('permission:attendance.import');

    // Devices (multiple per school)
    Route::post('devices/bulk-delete', [DeviceController::class, 'bulkDestroy'])->middleware('permission:attendance.devices');
    Route::get('devices', [DeviceController::class, 'index'])->middleware('permission:attendance.devices');
    Route::post('devices', [DeviceController::class, 'store'])->middleware('permission:attendance.devices');
    Route::get('devices/{id}', [DeviceController::class, 'show'])->middleware('permission:attendance.devices');
    Route::put('devices/{id}', [DeviceController::class, 'update'])->middleware('permission:attendance.devices');
    Route::delete('devices/{id}', [DeviceController::class, 'destroy'])->middleware('permission:attendance.devices');

    // Biometric ingestion (vendor-independent) + immutable logs
    Route::post('biometric/events', [BiometricController::class, 'events'])->middleware('permission:attendance.biometric');
    Route::get('biometric/logs', [BiometricController::class, 'logs'])->middleware('permission:attendance.view');
});
