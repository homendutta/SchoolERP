<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CMS & Public Portal — API Routes
|--------------------------------------------------------------------------
| Two surfaces:
|   /api/v1/cms/public/*  — READ-ONLY published content for the static website
|                            (+ throttled public contact/enquiry intake).
|   /api/v1/cms/*         — Admin CMS management (auth:sanctum + RBAC).
| Images use the Media Platform; contact forms flow through the Communication
| Engine; admission enquiries are captured only (Admissions is never auto-written).
*/

use App\Modules\Cms\Http\Controllers\CategoryController;
use App\Modules\Cms\Http\Controllers\CmsDashboardController;
use App\Modules\Cms\Http\Controllers\DownloadController;
use App\Modules\Cms\Http\Controllers\EnquiryController;
use App\Modules\Cms\Http\Controllers\EventController;
use App\Modules\Cms\Http\Controllers\FormController;
use App\Modules\Cms\Http\Controllers\GalleryController;
use App\Modules\Cms\Http\Controllers\MenuController;
use App\Modules\Cms\Http\Controllers\NewsController;
use App\Modules\Cms\Http\Controllers\NoticeController;
use App\Modules\Cms\Http\Controllers\PageController;
use App\Modules\Cms\Http\Controllers\PublicController;
use App\Modules\Cms\Http\Controllers\SettingController;
use App\Modules\Cms\Http\Controllers\SubmissionController;
use App\Modules\Cms\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

// -------------------- Public website API (read-only + throttled intake) --------------------
Route::prefix('cms/public')->group(function (): void {
    Route::get('homepage', [PublicController::class, 'homepage']);
    Route::get('settings', [PublicController::class, 'settings']);
    Route::get('menus', [PublicController::class, 'menus']);
    Route::get('notices', [PublicController::class, 'notices']);
    Route::get('news', [PublicController::class, 'news']);
    Route::get('events', [PublicController::class, 'events']);
    Route::get('gallery', [PublicController::class, 'gallery']);
    Route::get('videos', [PublicController::class, 'videos']);
    Route::get('downloads', [PublicController::class, 'downloads']);
    Route::get('staff', [PublicController::class, 'staff']);
    Route::get('pages/{slug}', [PublicController::class, 'page']);

    Route::middleware('throttle:20,1')->group(function (): void {
        Route::post('forms', [PublicController::class, 'submitForm']);
        Route::post('enquiries', [PublicController::class, 'submitEnquiry']);
    });
});

// -------------------- Admin CMS management (RBAC) --------------------
Route::middleware('auth:sanctum')->prefix('cms')->group(function (): void {
    $view = 'permission:cms.view';
    $manage = 'permission:cms.manage';

    Route::get('dashboard', [CmsDashboardController::class, 'overview'])->middleware($view);

    Route::get('settings', [SettingController::class, 'show'])->middleware($view);
    Route::put('settings', [SettingController::class, 'update'])->middleware($manage);

    $crud = function (string $name, string $controller) use ($view, $manage): void {
        Route::post("$name/bulk-delete", [$controller, 'bulkDestroy'])->middleware($manage);
        Route::get($name, [$controller, 'index'])->middleware($view);
        Route::post($name, [$controller, 'store'])->middleware($manage);
        Route::get("$name/{id}", [$controller, 'show'])->middleware($view);
        Route::put("$name/{id}", [$controller, 'update'])->middleware($manage);
        Route::delete("$name/{id}", [$controller, 'destroy'])->middleware($manage);
    };

    $crud('categories', CategoryController::class);
    $crud('pages', PageController::class);
    $crud('notices', NoticeController::class);
    $crud('news', NewsController::class);
    $crud('events', EventController::class);
    $crud('gallery', GalleryController::class);
    $crud('videos', VideoController::class);
    $crud('downloads', DownloadController::class);
    $crud('menus', MenuController::class);
    $crud('forms', FormController::class);

    // Enquiries + submissions (captured from the public site) — read + status only.
    Route::get('enquiries', [EnquiryController::class, 'index'])->middleware($view);
    Route::get('enquiries/{id}', [EnquiryController::class, 'show'])->middleware($view);
    Route::put('enquiries/{id}', [EnquiryController::class, 'update'])->middleware($manage);
    Route::get('submissions', [SubmissionController::class, 'index'])->middleware($view);
    Route::get('submissions/{id}', [SubmissionController::class, 'show'])->middleware($view);
    Route::put('submissions/{id}', [SubmissionController::class, 'update'])->middleware($manage);
});
