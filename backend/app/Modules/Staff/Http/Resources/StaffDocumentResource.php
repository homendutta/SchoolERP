<?php

declare(strict_types=1);

namespace App\Modules\Staff\Http\Resources;

use App\Modules\Staff\Models\StaffDocument;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin StaffDocument
 */
class StaffDocumentResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'staff_id' => $this->staff_id,
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
