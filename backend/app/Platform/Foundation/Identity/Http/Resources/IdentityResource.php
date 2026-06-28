<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Identity\Http\Resources;

use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin Identity
 */
class IdentityResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'school_id' => $this->school_id,
            'identity_number' => $this->identity_number,
            'identity_type' => $this->identity_type?->value,
            'public_identifier' => $this->public_identifier,
            'qr_payload' => $this->qr_payload,
            'barcode_value' => $this->barcode_value,
            'status' => $this->status?->value,
            'metadata' => $this->metadata,
            'owner' => $this->whenLoaded('owner', fn () => $this->owner ? [
                'id' => $this->owner->getKey(),
                'name' => $this->owner->getAttribute('name'),
                'type' => $this->identity_type?->value,
            ] : null),
            'owner_type' => $this->owner_type,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
