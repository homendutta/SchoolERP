<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Resources;

use App\Modules\Academic\Models\Section;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin Section
 */
class SectionResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'class_id' => $this->class_id,
            'class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass?->only(['id', 'name', 'code'])),
            'room_id' => $this->room_id,
            'room' => $this->whenLoaded('room', fn () => $this->room?->only(['id', 'name', 'code'])),
            'name' => $this->name,
            'capacity' => $this->capacity,
            'display_order' => $this->display_order,
            'status' => $this->status?->value,
            'archived' => $this->trashed(),
        ];
    }
}
