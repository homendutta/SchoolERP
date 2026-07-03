<?php

declare(strict_types=1);

namespace App\Modules\Lms\Services;

use App\Modules\Administration\Models\User;
use App\Modules\Lms\Models\Assignment;
use App\Modules\Lms\Models\Homework;
use App\Modules\Lms\Models\Lesson;
use App\Modules\Lms\Models\QuizAttempt;
use App\Modules\Lms\Models\Submission;

/**
 * Role-aware LMS dashboard. Teachers see their content + pending reviews;
 * students/parents see due work + progress. Reads only — no calculations owned.
 */
class LmsDashboardService
{
    public function __construct(
        private readonly LmsAuthorizationService $auth,
        private readonly ProgressService $progress,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $schoolId = (int) $user->school_id;

        if ($this->auth->isTeacher($user)) {
            return [
                'role' => 'teacher',
                'widgets' => [
                    'lessons' => Lesson::query()->where('school_id', $schoolId)->where('status', 'published')->count(),
                    'homework' => Homework::query()->where('school_id', $schoolId)->where('teacher_id', $user->id)->count(),
                    'assignments' => Assignment::query()->where('school_id', $schoolId)->where('teacher_id', $user->id)->count(),
                    'submissions_pending_review' => Submission::query()->where('school_id', $schoolId)->where('status', 'submitted')->count(),
                    'quiz_attempts' => QuizAttempt::query()->where('school_id', $schoolId)->count(),
                ],
            ];
        }

        // Student / parent — one entry per authorized child.
        $children = [];
        foreach ($this->auth->authorizedStudentIds($user) as $studentId) {
            $children[] = ['student_id' => $studentId, 'progress' => $this->progress->forStudent($studentId)];
        }

        return ['role' => 'student', 'children' => $children];
    }
}
