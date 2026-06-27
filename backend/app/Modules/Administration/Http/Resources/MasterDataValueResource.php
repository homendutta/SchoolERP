<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Resources;

use App\Modules\Administration\Models\MasterDataValue;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin MasterDataValue
 */
class MasterDataValueResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type_id' => $this->type_id,
            'type' => $this->whenLoaded('type', fn () => $this->type?->only(['id', 'name', 'slug'])),
            'label' => $this->label,
            'value' => $this->value,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'meta' => $this->meta,
            'archived' => $this->trashed(),
        ];
    }
}
