<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Resources;

use App\Modules\Academic\Models\Room;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin Room
 */
class RoomResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'room_type_id' => $this->room_type_id,
            'room_type' => $this->whenLoaded('roomType', fn () => $this->roomType?->only(['id', 'label', 'value'])),
            'code' => $this->code,
            'name' => $this->name,
            'capacity' => $this->capacity,
            'building' => $this->building,
            'display_order' => $this->display_order,
            'status' => $this->status?->value,
            'archived' => $this->trashed(),
        ];
    }
}
