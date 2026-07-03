<?php

declare(strict_types=1);

namespace App\Modules\Lms\Services;

use App\Modules\Administration\Models\User;
use App\Modules\Lms\Enums\ReviewAction;
use App\Modules\Lms\Enums\SubmissionStatus;
use App\Modules\Lms\Models\Review;
use App\Modules\Lms\Models\Submission;
use App\Modules\Students\Services\StudentTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseService;

/**
 * Teacher review engine. Reviews are append-only (comment / grade / return /
 * approve); the submission's current status reflects the latest action while the
 * submission version rows themselves stay immutable. Timeline + Audit + Comms.
 */
class ReviewService extends BaseService
{
    public function __construct(
        private readonly LmsAuthorizationService $auth,
        private readonly ActivityLogger $activity,
        private readonly StudentTimelineService $timeline,
        private readonly LmsHooks $hooks,
    ) {}

    /**
     * @param  array{action:string, comment?:string|null, marks?:float|null, subject_id?:int|null}  $data
     */
    public function review(User $user, Submission $submission, array $data): Review
    {
        // Teachers only, scoped to the subject they teach.
        if (isset($data['subject_id'])) {
            $this->auth->authorizeTeacherSubject($user, (int) $data['subject_id']);
        } else {
            $this->auth->requireTeacher($user);
        }

        return $this->transaction(function () use ($user, $submission, $data): Review {
            $action = ReviewAction::from($data['action']);

            $review = Review::query()->create([
                'school_id' => $submission->school_id,
                'submission_id' => $submission->id,
                'reviewer_id' => $user->id,
                'action' => $action->value,
                'comment' => $data['comment'] ?? null,
                'marks' => $data['marks'] ?? null,
            ]);

            $newStatus = match ($action) {
                ReviewAction::Grade => SubmissionStatus::Graded,
                ReviewAction::Return => SubmissionStatus::Returned,
                ReviewAction::Approve => SubmissionStatus::Approved,
                ReviewAction::Comment => $submission->status,
            };
            $submission->status = $newStatus->value;
            if ($action === ReviewAction::Grade && isset($data['marks'])) {
                $submission->marks = $data['marks'];
            }
            $submission->save();

            $event = $action === ReviewAction::Grade ? 'lms.assignment_graded' : 'lms.submission_reviewed';
            $this->timeline->record((int) $submission->student_id, $event, 'Work reviewed', $data['comment'] ?? null, [
                'submission_id' => $submission->id, 'action' => $action->value,
            ]);
            $this->activity->record($event, 'Submission '.$action->value, $review, [], (int) $submission->school_id, 'lms');
            $this->hooks->publish((int) $submission->school_id, $event, 'Submission reviewed', "A submission was {$action->value}ed.");

            return $review;
        });
    }
}
