<?php

declare(strict_types=1);

namespace App\Modules\Examination\Actions;

use App\Modules\Examination\Models\ExamSession;
use App\Modules\Examination\Models\ExamSubject;
use App\Modules\Examination\Services\StudentSubjectService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Support\Facades\DB;

/**
 * Auto-assign every CORE subject in a session to the current students of its
 * class/section. Electives are intentionally NOT auto-assigned (opt-in only),
 * which keeps optional subjects correct downstream.
 */
class AssignSubjectsAction implements Action
{
    use AsAction;

    public function __construct(
        private readonly StudentSubjectService $assignments,
        private readonly ActivityLogger $activity,
    ) {}

    /**
     * @return array{core_subjects:int, assignments:int}
     */
    public function handle(ExamSession $session): array
    {
        return DB::transaction(function () use ($session): array {
            $coreSubjects = ExamSubject::query()
                ->where('exam_session_id', $session->id)
                ->where('is_elective', false)
                ->get();

            $assignments = 0;
            foreach ($coreSubjects as $examSubject) {
                $assignments += $this->assignments->autoAssignCore($examSubject);
            }

            $this->activity->record('exam.subjects_assigned', "Auto-assigned core subjects for {$session->name}", $session, [
                'core_subjects' => $coreSubjects->count(),
                'assignments' => $assignments,
            ], $session->school_id, 'examination');

            return ['core_subjects' => $coreSubjects->count(), 'assignments' => $assignments];
        });
    }
}
