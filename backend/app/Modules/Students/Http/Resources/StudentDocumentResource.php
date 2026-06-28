<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Resources;

use App\Modules\Students\Models\StudentDocument;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin StudentDocument
 */
class StudentDocumentResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'student_id' => $this->student_id,
            'document_type_id' => $this->document_type_id,
            'document_type' => $this->whenLoaded('documentType', fn () => $this->documentType?->only(['id', 'label', 'value'])),
            'media_id' => $this->media_id,
            'media' => $this->whenLoaded('media', fn () => $this->media ? [
                'id' => $this->media->id,
                'uuid' => $this->media->uuid,
                'url' => $this->media->url(),
            ] : null),
            'title' => $this->title,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
