<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Resources;

use App\Modules\Academic\Models\SchoolClass;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin SchoolClass
 */
class ClassResource extends BaseResource
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
            'short_name' => $this->short_name,
            'slug' => $this->slug,
            'display_order' => $this->display_order,
            'status' => $this->status?->value,
            'version' => $this->version,
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
            'archived' => $this->trashed(),
        ];
    }
}
