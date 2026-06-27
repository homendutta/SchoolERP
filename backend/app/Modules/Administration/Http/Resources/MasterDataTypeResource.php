<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Resources;

use App\Modules\Administration\Models\MasterDataType;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin MasterDataType
 */
class MasterDataTypeResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'group' => $this->whenLoaded('group', fn () => $this->group?->only(['id', 'name', 'slug'])),
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'is_system' => (bool) $this->is_system,
            'values_count' => $this->whenCounted('values'),
            'archived' => $this->trashed(),
            'values' => MasterDataValueResource::collection($this->whenLoaded('values')),
        ];
    }
}
