<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Resources;

use App\Modules\Administration\Models\MasterDataGroup;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin MasterDataGroup
 */
class MasterDataGroupResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'is_system' => (bool) $this->is_system,
            'archived' => $this->trashed(),
            'types' => MasterDataTypeResource::collection($this->whenLoaded('types')),
        ];
    }
}
