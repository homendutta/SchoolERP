<?php

declare(strict_types=1);

namespace App\Modules\Examination\Actions;

use App\Modules\Examination\Models\ExamResult;
use App\Modules\Examination\Models\ExamSession;
use App\Modules\Students\Services\StudentTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;
use Illuminate\Support\Facades\DB;

/**
 * Publish a session's results. Writes the Audit Log and a Timeline entry per
 * student (a major examination event).
 */
class PublishResultsAction implements Action
{
    use AsAction;

    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly StudentTimelineService $timeline,
    ) {}

    /**
     * @return array{published:int}
     */
    public function handle(ExamSession $session): array
    {
        return DB::transaction(function () use ($session): array {
            $results = ExamResult::query()
                ->where('exam_session_id', $session->id)
                ->where('result_status', '!=', 'pending')
                ->get();

            foreach ($results as $result) {
                $result->update(['is_published' => true, 'published_at' => now()]);
                $this->timeline->record(
                    (int) $result->student_id,
                    'exam.result_published',
                    "Result published — {$session->name}",
                    null,
                    ['exam_session_id' => $session->id, 'percentage' => $result->percentage],
                );
            }

            $session->update(['status' => 'published']);

            $this->activity->record('exam.results_published', "Published results for {$session->name}", $session, [
                'published' => $results->count(),
            ], $session->school_id, 'examination');

            return ['published' => $results->count()];
        });
    }
}
