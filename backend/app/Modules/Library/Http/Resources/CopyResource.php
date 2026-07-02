<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Resources;

use App\Modules\Library\Models\Copy;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin Copy
 */
class CopyResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'book_id' => $this->book_id,
            'book' => $this->whenLoaded('book', fn () => $this->book?->title),
            'copy_number' => $this->copy_number,
            'identity_id' => $this->identity_id,
            // Barcode + QR come from the copy's platform Identity (generated dynamically).
            'identity_number' => $this->whenLoaded('copyIdentity', fn () => $this->copyIdentity?->identity_number),
            'barcode' => $this->whenLoaded('copyIdentity', fn () => $this->copyIdentity?->barcode_value),
            'qr_payload' => $this->whenLoaded('copyIdentity', fn () => $this->copyIdentity?->qr_payload),
            'location_id' => $this->location_id,
            'location' => $this->whenLoaded('location', fn () => $this->location?->name),
            'shelf' => $this->shelf,
            'rack' => $this->rack,
            'acquisition_date' => $this->acquisition_date?->toDateString(),
            'purchase_price' => $this->purchase_price,
            'condition' => $this->condition->value,
            'status' => $this->status->value,
        ];
    }
}
