<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Learning Management System — API Routes (prefix: /api/v1/lms)
|--------------------------------------------------------------------------
| Portal-authenticated only (no public APIs). Teachers manage content only for
| their assigned subjects; students access only their own data; parents only
| their children's. Files use the Media Platform; notifications the Communication
| Engine; homework/assignments/quizzes are INDEPENDENT of the Examination module.
*/

use App\Modules\Lms\Http\Controllers\AssignmentController;
use App\Modules\Lms\Http\Controllers\AttemptController;
use App\Modules\Lms\Http\Controllers\DiscussionController;
use App\Modules\Lms\Http\Controllers\HomeworkController;
use App\Modules\Lms\Http\Controllers\LessonController;
use App\Modules\Lms\Http\Controllers\LessonPlanController;
use App\Modules\Lms\Http\Controllers\LmsDashboardController;
use App\Modules\Lms\Http\Controllers\MaterialController;
use App\Modules\Lms\Http\Controllers\QuizController;
use App\Modules\Lms\Http\Controllers\ResourceController;
use App\Modules\Lms\Http\Controllers\ReviewController;
use App\Modules\Lms\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('lms')->group(function (): void {
    Route::get('dashboard', [LmsDashboardController::class, 'dashboard']);
    Route::get('progress', [LmsDashboardController::class, 'progress']);

    $crud = function (string $name, string $controller): void {
        Route::get($name, [$controller, 'index']);
        Route::post($name, [$controller, 'store']);
        Route::get("$name/{id}", [$controller, 'show']);
        Route::put("$name/{id}", [$controller, 'update']);
        Route::delete("$name/{id}", [$controller, 'destroy']);
    };

    $crud('lesson-plans', LessonPlanController::class);
    $crud('lessons', LessonController::class);
    $crud('materials', MaterialController::class);
    $crud('homework', HomeworkController::class);
    $crud('assignments', AssignmentController::class);
    $crud('resources', ResourceController::class);
    $crud('quizzes', QuizController::class);

    // Student submissions (immutable versions) + teacher reviews
    Route::get('submissions', [SubmissionController::class, 'index']);
    Route::post('submissions', [SubmissionController::class, 'store']);
    Route::post('reviews', [ReviewController::class, 'store']);

    // Quiz attempts
    Route::get('attempts', [AttemptController::class, 'index']);
    Route::post('attempts', [AttemptController::class, 'store']);

    // Classroom discussions (+ replies + moderation)
    Route::get('discussions', [DiscussionController::class, 'index']);
    Route::post('discussions', [DiscussionController::class, 'store']);
    Route::get('discussions/{id}', [DiscussionController::class, 'show']);
    Route::put('discussions/{id}', [DiscussionController::class, 'update']);
    Route::delete('discussions/{id}', [DiscussionController::class, 'destroy']);
    Route::post('discussions/{id}/posts', [DiscussionController::class, 'post']);
    Route::post('discussions/posts/{postId}/moderate', [DiscussionController::class, 'moderate']);
});
