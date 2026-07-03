<?php

declare(strict_types=1);

namespace App\Modules\Lms\Http\Controllers;

use App\Modules\Lms\Http\Resources\SimpleResource;
use App\Modules\Lms\Models\Quiz;
use App\Modules\Lms\Models\QuizAttempt;
use App\Modules\Lms\Services\LmsAuthorizationService;
use App\Modules\Lms\Services\QuizAttemptService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** LMS quiz attempts (learning quizzes only). */
class AttemptController extends BaseController
{
    public function __construct(
        private readonly QuizAttemptService $service,
        private readonly LmsAuthorizationService $auth,
    ) {}

    /** An authorized student's own attempts for a quiz. */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quiz_id' => ['required', 'integer'],
            'student_id' => ['required', 'integer'],
        ]);
        $this->auth->authorizeStudent($request->user(), (int) $validated['student_id']);

        $attempts = QuizAttempt::query()->where('quiz_id', $validated['quiz_id'])
            ->where('student_id', $validated['student_id'])->orderBy('attempt_number')->get();

        return $this->ok(SimpleResource::collection($attempts));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quiz_id' => ['required', 'integer', 'exists:lms_quizzes,id'],
            'student_id' => ['required', 'integer'],
            'responses' => ['nullable', 'array'],
            'started_at' => ['nullable', 'date'],
            'time_taken' => ['nullable', 'integer', 'min:0'],
        ]);

        $quiz = Quiz::query()->with('questions')->findOrFail($validated['quiz_id']);
        $attempt = $this->service->attempt($request->user(), $quiz, (int) $validated['student_id'], $validated);

        return $this->ok(new SimpleResource($attempt), 'Attempt recorded.', 201);
    }
}
