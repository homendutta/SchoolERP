<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Resources;

use App\Modules\Admissions\Models\AdmissionApplication;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin AdmissionApplication
 */
class ApplicationResource extends BaseResource
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
            'application_number' => $this->application_number,
            'academic_year_id' => $this->academic_year_id,
            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear?->only(['id', 'name'])),
            'class_id' => $this->class_id,
            'class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass?->only(['id', 'name'])),
            'section_id' => $this->section_id,
            'section' => $this->whenLoaded('section', fn () => $this->section?->only(['id', 'name'])),
            'enquiry_id' => $this->enquiry_id,

            'student_name' => $this->student_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'blood_group' => $this->blood_group,
            'nationality' => $this->nationality,
            'religion' => $this->religion,

            'guardian_name' => $this->guardian_name,
            'guardian_relation' => $this->guardian_relation,
            'guardian_phone' => $this->guardian_phone,
            'guardian_email' => $this->guardian_email,
            'guardian_occupation' => $this->guardian_occupation,

            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'previous_school' => $this->previous_school,
            'previous_class' => $this->previous_class,

            'remarks' => $this->remarks,
            'status' => $this->status?->value,
            'verification_status' => $this->verification_status?->value,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'enrolled_student_id' => $this->enrolled_student_id,

            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
            'approval_steps' => ApprovalStepResource::collection($this->whenLoaded('approvalSteps')),
            'archived' => $this->trashed(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
