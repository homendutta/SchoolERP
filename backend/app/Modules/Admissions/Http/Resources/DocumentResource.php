<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Resources;

use App\Modules\Admissions\Models\AdmissionDocument;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin AdmissionDocument
 */
class DocumentResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'application_id' => $this->application_id,
            'document_type_id' => $this->document_type_id,
            'document_type' => $this->whenLoaded('documentType', fn () => $this->documentType?->only(['id', 'label', 'value'])),
            'media_id' => $this->media_id,
            'title' => $this->title,
            'status' => $this->status?->value,
            'remarks' => $this->remarks,
            'verified_by' => $this->verified_by,
            'verified_at' => $this->verified_at?->toIso8601String(),
        ];
    }
}
