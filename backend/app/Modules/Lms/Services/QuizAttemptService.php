<?php

declare(strict_types=1);

namespace App\Modules\Lms\Services;

use App\Modules\Administration\Models\User;
use App\Modules\Lms\Models\Quiz;
use App\Modules\Lms\Models\QuizAttempt;
use App\Modules\Lms\Models\QuizQuestion;
use App\Modules\Students\Services\StudentTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Exceptions\DomainException;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Carbon;

/**
 * LMS quiz attempt engine (learning quizzes only — NOT Examination exams).
 * Enforces the configurable attempt limit, auto-grades objective answers and
 * records score/time/attempt number. Timeline + Audit written.
 */
class QuizAttemptService extends BaseService
{
    public function __construct(
        private readonly LmsAuthorizationService $auth,
        private readonly ActivityLogger $activity,
        private readonly StudentTimelineService $timeline,
    ) {}

    /**
     * Submit a quiz attempt for an authorized student.
     *
     * @param  array{responses?:array<int, mixed>, started_at?:string|null, time_taken?:int|null}  $data
     */
    public function attempt(User $user, Quiz $quiz, int $studentId, array $data): QuizAttempt
    {
        $student = $this->auth->authorizeStudent($user, $studentId);

        $used = QuizAttempt::query()->where('quiz_id', $quiz->id)->where('student_id', $student->id)->count();
        if ($used >= (int) $quiz->max_attempts) {
            throw new DomainException('You have reached the maximum number of attempts.', 422, 'ATTEMPT_LIMIT');
        }

        return $this->transaction(function () use ($quiz, $student, $data, $used): QuizAttempt {
            $responses = $data['responses'] ?? [];
            [$score, $total] = $this->grade($quiz, is_array($responses) ? $responses : []);
            $passing = $quiz->passing_marks !== null ? (float) $quiz->passing_marks : null;

            $startedAt = isset($data['started_at']) ? Carbon::parse($data['started_at']) : Carbon::now();
            $attempt = QuizAttempt::query()->create([
                'school_id' => $quiz->school_id,
                'quiz_id' => $quiz->id,
                'student_id' => $student->id,
                'attempt_number' => $used + 1,
                'started_at' => $startedAt,
                'finished_at' => Carbon::now(),
                'score' => $score,
                'time_taken' => $data['time_taken'] ?? Carbon::now()->diffInSeconds($startedAt),
                'passed' => $passing !== null ? $score >= $passing : null,
                'responses' => $responses,
            ]);

            $this->timeline->record((int) $student->id, 'lms.quiz_attempt', 'Quiz attempted', null, [
                'quiz_id' => $quiz->id, 'score' => $score, 'total' => $total,
            ]);
            $this->activity->record('lms.quiz_attempted', 'Quiz attempted', $attempt, [
                'score' => $score, 'total' => $total,
            ], (int) $quiz->school_id, 'lms');

            return $attempt;
        });
    }

    /**
     * Auto-grade objective answers. Short/fill answers compared case-insensitively.
     *
     * @param  array<int, mixed>  $responses  keyed by question id
     * @return array{0: float, 1: float}
     */
    private function grade(Quiz $quiz, array $responses): array
    {
        $score = 0.0;
        $total = 0.0;
        foreach ($quiz->questions as $question) {
            /** @var QuizQuestion $question */
            $total += (float) $question->marks;
            $given = $responses[$question->id] ?? ($responses[(string) $question->id] ?? null);
            if ($given !== null && $this->isCorrect($question, $given)) {
                $score += (float) $question->marks;
            }
        }

        return [round($score, 2), round($total, 2)];
    }

    private function isCorrect(QuizQuestion $question, mixed $given): bool
    {
        $answer = $question->answer;
        if ($answer === null) {
            return false;
        }
        $expected = is_array($answer) ? array_map(fn ($v) => $this->norm($v), $answer) : [$this->norm($answer)];
        $actual = is_array($given) ? array_map(fn ($v) => $this->norm($v), $given) : [$this->norm($given)];
        sort($expected);
        sort($actual);

        return $expected === $actual;
    }

    private function norm(mixed $v): string
    {
        return strtolower(trim((string) $v));
    }
}
