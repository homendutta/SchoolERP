<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Resources;

use App\Modules\Admissions\Models\AdmissionWorkflowStep;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin AdmissionWorkflowStep
 */
class WorkflowStepResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'name' => $this->name,
            'role_slug' => $this->role_slug,
            'sort_order' => $this->sort_order,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
