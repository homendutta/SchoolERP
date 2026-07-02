<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Resources;

use App\Modules\Library\Models\Reservation;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin Reservation
 */
class ReservationResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'identity_id' => $this->identity_id,
            'identity_number' => $this->whenLoaded('identity', fn () => $this->identity?->identity_number),
            'borrower' => $this->whenLoaded('owner', fn () => $this->owner?->getAttribute('name')),
            'book_id' => $this->book_id,
            'book' => $this->whenLoaded('book', fn () => $this->book?->title),
            'status' => $this->status->value,
            'queue_position' => $this->queue_position,
            'reserved_at' => $this->reserved_at?->toIso8601String(),
            'available_at' => $this->available_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
        ];
    }
}
