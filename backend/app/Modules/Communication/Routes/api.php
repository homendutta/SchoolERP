<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Communication — API Routes (prefix: /api/v1/communication)
|--------------------------------------------------------------------------
| The central communication hub. Every business module publishes here — none
| sends Email/SMS/Push/In-App directly. Templates, the message queue + delivery
| tracking, configurable channels, user preferences, announcements, circulars
| (Media attachments) and the dashboard.
*/

use App\Modules\Communication\Http\Controllers\AnnouncementController;
use App\Modules\Communication\Http\Controllers\ChannelController;
use App\Modules\Communication\Http\Controllers\CircularController;
use App\Modules\Communication\Http\Controllers\CommunicationDashboardController;
use App\Modules\Communication\Http\Controllers\MessageController;
use App\Modules\Communication\Http\Controllers\PreferenceController;
use App\Modules\Communication\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('communication')->group(function (): void {
    $view = 'permission:communication.view';
    $manage = 'permission:communication.manage';
    $send = 'permission:communication.send';

    Route::get('dashboard', [CommunicationDashboardController::class, 'overview'])->middleware($view);

    // Templates
    Route::post('templates/bulk-delete', [TemplateController::class, 'bulkDestroy'])->middleware($manage);
    Route::get('templates', [TemplateController::class, 'index'])->middleware($view);
    Route::post('templates', [TemplateController::class, 'store'])->middleware($manage);
    Route::get('templates/{id}', [TemplateController::class, 'show'])->middleware($view);
    Route::put('templates/{id}', [TemplateController::class, 'update'])->middleware($manage);
    Route::delete('templates/{id}', [TemplateController::class, 'destroy'])->middleware($manage);

    // Messages (publish is the only send path) + delivery tracking
    Route::get('messages/scheduled', [MessageController::class, 'scheduled'])->middleware($view);
    Route::get('messages', [MessageController::class, 'index'])->middleware($view);
    Route::post('messages', [MessageController::class, 'publish'])->middleware($send);
    Route::get('messages/{id}', [MessageController::class, 'show'])->middleware($view);
    Route::post('messages/{id}/retry', [MessageController::class, 'retry'])->middleware($send);
    Route::post('messages/{id}/read', [MessageController::class, 'markRead'])->middleware($view);
    Route::post('messages/{id}/cancel', [MessageController::class, 'cancel'])->middleware($manage);

    // Queue worker
    Route::post('queue/process', [MessageController::class, 'process'])->middleware($send);

    // Channels (configurable settings + provider registry)
    Route::get('channels', [ChannelController::class, 'index'])->middleware($view);
    Route::post('channels', [ChannelController::class, 'store'])->middleware($manage);

    // User preferences
    Route::get('preferences', [PreferenceController::class, 'index'])->middleware($view);
    Route::put('preferences', [PreferenceController::class, 'update'])->middleware($view);

    // Announcements
    Route::post('announcements/bulk-delete', [AnnouncementController::class, 'bulkDestroy'])->middleware($manage);
    Route::get('announcements', [AnnouncementController::class, 'index'])->middleware($view);
    Route::post('announcements', [AnnouncementController::class, 'store'])->middleware($send);
    Route::get('announcements/{id}', [AnnouncementController::class, 'show'])->middleware($view);
    Route::put('announcements/{id}', [AnnouncementController::class, 'update'])->middleware($manage);
    Route::delete('announcements/{id}', [AnnouncementController::class, 'destroy'])->middleware($manage);

    // Circulars (Media attachment)
    Route::post('circulars/bulk-delete', [CircularController::class, 'bulkDestroy'])->middleware($manage);
    Route::get('circulars', [CircularController::class, 'index'])->middleware($view);
    Route::post('circulars', [CircularController::class, 'store'])->middleware($send);
    Route::get('circulars/{id}', [CircularController::class, 'show'])->middleware($view);
    Route::put('circulars/{id}', [CircularController::class, 'update'])->middleware($manage);
    Route::delete('circulars/{id}', [CircularController::class, 'destroy'])->middleware($manage);
});
