<?php

declare(strict_types=1);

namespace App\Modules\Staff\Http\Resources;

use App\Modules\Staff\Models\Staff;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin Staff
 */
class StaffResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'school_id' => $this->school_id,
            'identity_id' => $this->identity_id,
            'employee_number' => $this->employee_number,
            'name' => $this->name,
            'gender_id' => $this->gender_id,
            'gender' => $this->whenLoaded('gender', fn () => $this->gender?->only(['id', 'label', 'value'])),
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'marital_status' => $this->marital_status,
            'blood_group_id' => $this->blood_group_id,
            'blood_group' => $this->whenLoaded('bloodGroup', fn () => $this->bloodGroup?->only(['id', 'label', 'value'])),

            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,

            'department_id' => $this->department_id,
            'department' => $this->whenLoaded('department', fn () => $this->department?->only(['id', 'label', 'value'])),
            'designation_id' => $this->designation_id,
            'designation' => $this->whenLoaded('designation', fn () => $this->designation?->only(['id', 'label', 'value'])),
            'employment_type' => $this->employment_type?->value,
            'joining_date' => $this->joining_date?->toDateString(),
            'confirmation_date' => $this->confirmation_date?->toDateString(),
            'reporting_manager_id' => $this->reporting_manager_id,
            'reporting_manager' => $this->whenLoaded('reportingManager', fn () => $this->reportingManager?->only(['id', 'name', 'employee_number'])),
            'is_teaching' => (bool) $this->is_teaching,

            'status' => $this->status?->value,
            'photo_media_id' => $this->photo_media_id,
            'notes' => $this->notes,

            'qualifications' => QualificationResource::collection($this->whenLoaded('qualifications')),
            'experiences' => ExperienceResource::collection($this->whenLoaded('experiences')),
            'documents' => StaffDocumentResource::collection($this->whenLoaded('documents')),

            'archived' => $this->trashed(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
