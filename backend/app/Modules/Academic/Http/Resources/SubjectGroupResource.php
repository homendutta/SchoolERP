<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Resources;

use App\Modules\Academic\Models\SubjectGroup;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin SubjectGroup
 */
class SubjectGroupResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'code' => $this->code,
            'name' => $this->name,
            'slug' => $this->slug,
            'display_order' => $this->display_order,
            'status' => $this->status?->value,
            'subjects' => SubjectResource::collection($this->whenLoaded('subjects')),
            'subject_ids' => $this->whenLoaded('subjects', fn () => $this->subjects->pluck('id')),
            'archived' => $this->trashed(),
        ];
    }
}
