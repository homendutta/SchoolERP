<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Resources;

use App\Modules\Students\Models\Student;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin Student
 */
class StudentResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $current = $this->relationLoaded('currentRecord') ? $this->currentRecord : null;

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'school_id' => $this->school_id,
            'identity_id' => $this->identity_id,
            'admission_number' => $this->admission_number,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'religion' => $this->religion,
            'nationality' => $this->nationality,
            'category' => $this->category,

            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,

            'blood_group_id' => $this->blood_group_id,
            'blood_group' => $this->whenLoaded('bloodGroup', fn () => $this->bloodGroup?->only(['id', 'label', 'value'])),
            'allergies' => $this->allergies,
            'disabilities' => $this->disabilities,
            'medical_notes' => $this->medical_notes,
            'emergency_instructions' => $this->emergency_instructions,

            'notes' => $this->notes,
            'status' => $this->status?->value,
            'enrolled_on' => $this->enrolled_on?->toDateString(),
            'photo_media_id' => $this->photo_media_id,

            'current_record' => $current ? [
                'id' => $current->id,
                'academic_year' => $current->academicYear?->only(['id', 'name']),
                'class' => $current->schoolClass?->only(['id', 'name']),
                'section' => $current->section?->only(['id', 'name']),
                'roll_number' => $current->roll_number,
            ] : null,

            'guardians' => $this->whenLoaded('guardians', fn () => $this->guardians->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'phone' => $g->phone,
                'parent_number' => $g->parent_number,
                'relationship_type_id' => $g->pivot->relationship_type_id ?? null,
                'is_primary' => (bool) ($g->pivot->is_primary ?? false),
                'emergency_contact' => (bool) ($g->pivot->emergency_contact ?? false),
                'pickup_authorized' => (bool) ($g->pivot->pickup_authorized ?? false),
                'financial_responsible' => (bool) ($g->pivot->financial_responsible ?? false),
                'notes' => $g->pivot->notes ?? null,
            ])),

            'archived' => $this->trashed(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
