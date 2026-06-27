<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Resources;

use App\Modules\Academic\Models\Subject;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin Subject
 */
class SubjectResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'subject_type_id' => $this->subject_type_id,
            'subject_type' => $this->whenLoaded('subjectType', fn () => $this->subjectType?->only(['id', 'label', 'value'])),
            'code' => $this->code,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'slug' => $this->slug,
            'theory' => (bool) $this->theory,
            'practical' => (bool) $this->practical,
            'credits' => $this->credits,
            'display_order' => $this->display_order,
            'status' => $this->status?->value,
            'archived' => $this->trashed(),
        ];
    }
}
