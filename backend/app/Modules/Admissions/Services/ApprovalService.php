<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Services;

use App\Modules\Admissions\Enums\ApplicationStatus;
use App\Modules\Admissions\Enums\ApprovalStepStatus;
use App\Modules\Admissions\Models\AdmissionApplication;
use App\Modules\Admissions\Models\AdmissionApprovalStep;
use App\Modules\Admissions\Models\AdmissionWorkflowStep;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Facades\Auth;

/**
 * Configurable admission approval. Instantiates the school's workflow steps onto
 * an application, then advances them. The workflow is data-driven (one-step or
 * multi-step); nothing about the sequence is hardcoded.
 */
class ApprovalService extends BaseService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    /**
     * Begin the approval workflow for an application: copy the school's active
     * workflow-step definitions into per-application instances. Falls back to a
     * single implicit "Approval" step when no workflow is configured.
     */
    public function start(AdmissionApplication $application): AdmissionApplication
    {
        return $this->transaction(function () use ($application): AdmissionApplication {
            if ($application->approvalSteps()->exists()) {
                throw BusinessRuleException::make('Approval workflow already started.', 'WORKFLOW_STARTED');
            }

            $definitions = AdmissionWorkflowStep::query()
                ->where('school_id', $application->school_id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            if ($definitions->isEmpty()) {
                $definitions = collect([new AdmissionWorkflowStep([
                    'school_id' => $application->school_id,
                    'name' => 'Approval',
                    'sort_order' => 1,
                ])]);
            }

            foreach ($definitions as $index => $definition) {
                AdmissionApprovalStep::create([
                    'school_id' => $application->school_id,
                    'application_id' => $application->id,
                    'workflow_step_id' => $definition->exists ? $definition->id : null,
                    'name' => $definition->name,
                    'role_slug' => $definition->role_slug,
                    'sort_order' => $definition->sort_order ?: $index + 1,
                    'status' => ApprovalStepStatus::Pending->value,
                ]);
            }

            $application->forceFill(['status' => ApplicationStatus::UnderReview->value])->save();

            return $application->refresh();
        });
    }

    /**
     * Record a decision on one approval step and recompute the application's
     * overall status (rejected if any step is rejected; approved when all pass).
     */
    public function act(
        AdmissionApprovalStep $step,
        ApprovalStepStatus $decision,
        ?string $remarks = null,
    ): AdmissionApplication {
        return $this->transaction(function () use ($step, $decision, $remarks): AdmissionApplication {
            $step->forceFill([
                'status' => $decision->value,
                'actor_id' => Auth::id(),
                'acted_at' => now(),
                'remarks' => $remarks,
            ])->save();

            /** @var AdmissionApplication $application */
            $application = $step->application()->firstOrFail();
            $this->recomputeStatus($application);

            $this->activity->record(
                'admission.approval',
                "Step '{$step->name}' → {$decision->label()} on {$application->application_number}",
                $application,
                ['step' => $step->name, 'decision' => $decision->value],
                $application->school_id,
                'admissions',
            );

            return $application->refresh();
        });
    }

    private function recomputeStatus(AdmissionApplication $application): void
    {
        $steps = $application->approvalSteps()->get();

        if ($steps->contains(fn ($s) => $s->status === ApprovalStepStatus::Rejected)) {
            $application->forceFill(['status' => ApplicationStatus::Rejected->value])->save();

            return;
        }

        $allApproved = $steps->isNotEmpty()
            && $steps->every(fn ($s) => $s->status === ApprovalStepStatus::Approved);

        if ($allApproved) {
            $application->forceFill([
                'status' => ApplicationStatus::Approved->value,
                'approved_at' => now(),
            ])->save();
        }
    }
}
