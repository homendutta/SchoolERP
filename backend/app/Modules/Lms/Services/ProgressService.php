<?php

declare(strict_types=1);

namespace App\Modules\Lms\Services;

use App\Modules\Lms\Models\Assignment;
use App\Modules\Lms\Models\Homework;
use App\Modules\Lms\Models\LessonCompletion;
use App\Modules\Lms\Models\QuizAttempt;
use App\Modules\Lms\Models\Submission;

/** Operational learning progress for a student (no analytics/AI). */
class ProgressService
{
    /**
     * @return array<string, mixed>
     */
    public function forStudent(int $studentId): array
    {
        $homeworkSubs = Submission::query()->where('submittable_type', Homework::class)
            ->where('student_id', $studentId)->distinct('submittable_id')->count('submittable_id');
        $assignmentSubs = Submission::query()->where('submittable_type', Assignment::class)
            ->where('student_id', $studentId)->distinct('submittable_id')->count('submittable_id');
        $attempts = QuizAttempt::query()->where('student_id', $studentId)->get(['quiz_id', 'score']);

        return [
            'lessons_completed' => LessonCompletion::query()->where('student_id', $studentId)->count(),
            'homework_submitted' => $homeworkSubs,
            'assignments_submitted' => $assignmentSubs,
            'quizzes_completed' => $attempts->pluck('quiz_id')->unique()->count(),
            'average_quiz_score' => $attempts->count() > 0 ? round((float) $attempts->avg('score'), 2) : 0,
        ];
    }
}
