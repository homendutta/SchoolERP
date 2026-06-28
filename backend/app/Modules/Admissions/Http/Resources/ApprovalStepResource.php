<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Resources;

use App\Modules\Admissions\Models\AdmissionApprovalStep;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin AdmissionApprovalStep
 */
class ApprovalStepResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'application_id' => $this->application_id,
            'workflow_step_id' => $this->workflow_step_id,
            'name' => $this->name,
            'role_slug' => $this->role_slug,
            'sort_order' => $this->sort_order,
            'status' => $this->status?->value,
            'actor_id' => $this->actor_id,
            'acted_at' => $this->acted_at?->toIso8601String(),
            'remarks' => $this->remarks,
        ];
    }
}
