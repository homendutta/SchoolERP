<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Finance — API Routes (prefix: /api/v1/finance)
|--------------------------------------------------------------------------
| Fee definition (categories/masters/structures), student assignment,
| installments, discounts/scholarships/sibling/fines, payments + allocation,
| refunds, adjustments, the independent ledger, due tracking, defaulters,
| receipts, the gateway abstraction and the dashboard. Reuses the Number
| Generator (receipt + transaction numbers), Master Data (payment methods) and
| the Identity Platform (receipt QR).
*/

use App\Modules\Finance\Http\Controllers\AdjustmentController;
use App\Modules\Finance\Http\Controllers\DefaulterController;
use App\Modules\Finance\Http\Controllers\DiscountController;
use App\Modules\Finance\Http\Controllers\DueTrackingController;
use App\Modules\Finance\Http\Controllers\FeeCategoryController;
use App\Modules\Finance\Http\Controllers\FeeMasterController;
use App\Modules\Finance\Http\Controllers\FeeStructureController;
use App\Modules\Finance\Http\Controllers\FinanceDashboardController;
use App\Modules\Finance\Http\Controllers\FineRuleController;
use App\Modules\Finance\Http\Controllers\GatewayController;
use App\Modules\Finance\Http\Controllers\InstallmentController;
use App\Modules\Finance\Http\Controllers\LedgerController;
use App\Modules\Finance\Http\Controllers\PaymentController;
use App\Modules\Finance\Http\Controllers\RefundController;
use App\Modules\Finance\Http\Controllers\ScholarshipController;
use App\Modules\Finance\Http\Controllers\SiblingRuleController;
use App\Modules\Finance\Http\Controllers\StudentFeeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('finance')->group(function (): void {
    $view = 'permission:finance.view';
    $manage = 'permission:finance.manage';
    $collect = 'permission:finance.collect';
    $refund = 'permission:finance.refund';

    Route::get('dashboard', [FinanceDashboardController::class, 'overview'])->middleware($view);

    $crud = function (string $base, string $controller) use ($view, $manage): void {
        Route::post("{$base}/bulk-delete", [$controller, 'bulkDestroy'])->middleware($manage);
        Route::get($base, [$controller, 'index'])->middleware($view);
        Route::post($base, [$controller, 'store'])->middleware($manage);
        Route::get("{$base}/{id}", [$controller, 'show'])->middleware($view);
        Route::put("{$base}/{id}", [$controller, 'update'])->middleware($manage);
        Route::delete("{$base}/{id}", [$controller, 'destroy'])->middleware($manage);
    };

    // Configurable definitions
    $crud('categories', FeeCategoryController::class);
    $crud('masters', FeeMasterController::class);
    $crud('structures', FeeStructureController::class);
    $crud('discounts', DiscountController::class);
    $crud('scholarships', ScholarshipController::class);
    $crud('sibling-discounts', SiblingRuleController::class);
    $crud('fines', FineRuleController::class);
    $crud('installments', InstallmentController::class);

    // Student fees + assignment + concessions
    Route::post('student-fees/assign', [StudentFeeController::class, 'assign'])->middleware($manage);
    Route::post('student-fees/{id}/discount', [StudentFeeController::class, 'applyDiscount'])->middleware($manage);
    Route::post('student-fees/{id}/scholarship', [StudentFeeController::class, 'applyScholarship'])->middleware($manage);
    Route::post('student-fees/{id}/sibling-discount', [StudentFeeController::class, 'applySibling'])->middleware($manage);
    Route::get('student-fees', [StudentFeeController::class, 'index'])->middleware($view);
    Route::get('student-fees/{id}', [StudentFeeController::class, 'show'])->middleware($view);

    // Payments + receipts
    Route::get('payments', [PaymentController::class, 'index'])->middleware($view);
    Route::post('payments', [PaymentController::class, 'store'])->middleware($collect);
    Route::get('payments/{id}', [PaymentController::class, 'show'])->middleware($view);
    Route::get('payments/{id}/receipt', [PaymentController::class, 'receipt'])->middleware($view);

    // Refunds + adjustments
    Route::get('refunds', [RefundController::class, 'index'])->middleware($view);
    Route::post('refunds', [RefundController::class, 'store'])->middleware($refund);
    Route::get('adjustments', [AdjustmentController::class, 'index'])->middleware($view);
    Route::post('adjustments', [AdjustmentController::class, 'store'])->middleware($manage);

    // Ledger (read-only), due tracking, defaulters
    Route::get('ledger', [LedgerController::class, 'index'])->middleware($view);
    Route::get('due-tracking', [DueTrackingController::class, 'show'])->middleware($view);
    Route::get('defaulters', [DefaulterController::class, 'index'])->middleware($view);

    // Online payment gateway abstraction
    Route::get('gateways', [GatewayController::class, 'providers'])->middleware($view);
    Route::post('gateways/initiate', [GatewayController::class, 'initiate'])->middleware($collect);
});
