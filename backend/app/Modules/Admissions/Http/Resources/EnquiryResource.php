<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Resources;

use App\Modules\Admissions\Models\AdmissionEnquiry;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin AdmissionEnquiry
 */
class EnquiryResource extends BaseResource
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
            'academic_year_id' => $this->academic_year_id,
            'enquiry_number' => $this->enquiry_number,
            'student_name' => $this->student_name,
            'guardian_name' => $this->guardian_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'class_interested' => $this->class_interested,
            'source_id' => $this->source_id,
            'source' => $this->whenLoaded('source', fn () => $this->source?->only(['id', 'label', 'value'])),
            'status' => $this->status?->value,
            'remarks' => $this->remarks,
            'follow_up_date' => $this->follow_up_date?->toDateString(),
            'converted_application_id' => $this->converted_application_id,
            'archived' => $this->trashed(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
