<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Controllers;

use App\Modules\Admissions\Enums\ApprovalStepStatus;
use App\Modules\Admissions\Http\Requests\ApprovalActionRequest;
use App\Modules\Admissions\Http\Resources\ApplicationResource;
use App\Modules\Admissions\Models\AdmissionApplication;
use App\Modules\Admissions\Models\AdmissionApprovalStep;
use App\Modules\Admissions\Services\ApprovalService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

class ApprovalController extends BaseController
{
    public function __construct(private readonly ApprovalService $service) {}

    /** Instantiate the configured workflow steps onto an application. */
    public function start(int|string $id): JsonResponse
    {
        $application = AdmissionApplication::query()->findOrFail($id);
        $updated = $this->service->start($application);

        return $this->ok(
            new ApplicationResource($updated->load('approvalSteps')),
            'Approval workflow started.',
        );
    }

    /** Record a decision on a single approval step. */
    public function act(ApprovalActionRequest $request, int|string $stepId): JsonResponse
    {
        $step = AdmissionApprovalStep::query()->findOrFail($stepId);
        $decision = ApprovalStepStatus::from((string) $request->validated('decision'));

        $application = $this->service->act($step, $decision, $request->validated('remarks'));

        return $this->ok(
            new ApplicationResource($application->load('approvalSteps')),
            'Approval step recorded.',
        );
    }
}
