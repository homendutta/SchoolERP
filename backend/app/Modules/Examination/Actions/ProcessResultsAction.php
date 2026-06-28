<?php

declare(strict_types=1);

namespace App\Modules\Examination\Actions;

use App\Modules\Examination\Models\ExamSession;
use App\Modules\Examination\Services\ResultProcessingService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Actions\Action;
use App\Platform\Shared\Actions\AsAction;

/**
 * Process (or re-process) results for a session. Uses configurable grading +
 * ranking and respects each student's assigned subjects.
 */
class ProcessResultsAction implements Action
{
    use AsAction;

    public function __construct(
        private readonly ResultProcessingService $processor,
        private readonly ActivityLogger $activity,
    ) {}

    /**
     * @return array{processed:int}
     */
    public function handle(ExamSession $session): array
    {
        $result = $this->processor->process($session);

        $this->activity->record('exam.results_processed', "Processed results for {$session->name}", $session, $result, $session->school_id, 'examination');

        return $result;
    }
}
