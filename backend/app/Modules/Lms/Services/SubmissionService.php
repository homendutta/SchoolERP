<?php

declare(strict_types=1);

namespace App\Modules\Lms\Services;

use App\Modules\Administration\Models\User;
use App\Modules\Lms\Enums\SubmissionStatus;
use App\Modules\Lms\Models\Assignment;
use App\Modules\Lms\Models\Homework;
use App\Modules\Lms\Models\Submission;
use App\Modules\Students\Services\StudentTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Exceptions\DomainException;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * The student submission engine (homework + assignments). Submissions are
 * IMMUTABLE: every submit creates a new version row; nothing is ever overwritten.
 * Files are Media references. Timeline + Audit + Communication on each submit.
 */
class SubmissionService extends BaseService
{
    public function __construct(
        private readonly LmsAuthorizationService $auth,
        private readonly ActivityLogger $activity,
        private readonly StudentTimelineService $timeline,
        private readonly LmsHooks $hooks,
    ) {}

    /** @return class-string<Model> */
    private function resolveModel(string $type): string
    {
        return match ($type) {
            'homework' => Homework::class,
            'assignment' => Assignment::class,
            default => throw new DomainException('Unknown submittable type.', 422, 'BAD_TYPE'),
        };
    }

    /**
     * Submit for a homework/assignment on behalf of an authorized student.
     *
     * @param  array<string, mixed>  $data
     */
    public function submit(User $user, string $type, int $submittableId, int $studentId, array $data): Submission
    {
        $student = $this->auth->authorizeStudent($user, $studentId);
        $modelClass = $this->resolveModel($type);
        /** @var Homework|Assignment $submittable */
        $submittable = $modelClass::query()->findOrFail($submittableId);

        $due = $submittable->getAttribute('due_date');
        $isLate = $due !== null && Carbon::now()->gt(Carbon::parse($due)->endOfDay());
        if ($isLate && ! $submittable->getAttribute('allow_late')) {
            throw new DomainException('This submission is past due and late submissions are not allowed.', 422, 'LATE_NOT_ALLOWED');
        }

        return $this->transaction(function () use ($submittable, $modelClass, $submittableId, $student, $isLate, $data): Submission {
            $version = (int) Submission::query()
                ->where('submittable_type', $modelClass)->where('submittable_id', $submittableId)
                ->where('student_id', $student->id)->max('version') + 1;

            $submission = Submission::query()->create([
                'school_id' => $submittable->getAttribute('school_id'),
                'submittable_type' => $modelClass,
                'submittable_id' => $submittableId,
                'student_id' => $student->id,
                'version' => $version,
                'content' => $data['content'] ?? null,
                'attachments' => $data['attachments'] ?? null,
                'links' => $data['links'] ?? null,
                'submitted_at' => Carbon::now(),
                'is_late' => $isLate,
                'status' => $isLate ? SubmissionStatus::Late->value : SubmissionStatus::Submitted->value,
            ]);

            $this->timeline->record((int) $student->id, 'lms.submission', 'Work submitted', null, [
                'submission_id' => $submission->id, 'version' => $version,
            ]);
            $this->activity->record('lms.submission_received', 'Submission received', $submission, [
                'version' => $version,
            ], (int) $submission->school_id, 'lms');
            $this->hooks->publish((int) $submission->school_id, 'lms.submission_received', 'Submission received', "A submission (v{$version}) was received.");

            return $submission;
        });
    }

    /**
     * List submission history for a homework/assignment + student (immutable versions).
     *
     * @return array<int, array<string, mixed>>
     */
    public function history(string $type, int $submittableId, int $studentId): array
    {
        $modelClass = $this->resolveModel($type);

        return Submission::query()
            ->where('submittable_type', $modelClass)->where('submittable_id', $submittableId)
            ->where('student_id', $studentId)->orderBy('version')->get()
            ->map(fn (Submission $s) => [
                'id' => $s->id,
                'version' => $s->version,
                'submitted_at' => $s->submitted_at?->toDateTimeString(),
                'is_late' => (bool) $s->is_late,
                'status' => $s->status->value,
                'marks' => $s->marks !== null ? (float) $s->marks : null,
            ])->all();
    }
}
