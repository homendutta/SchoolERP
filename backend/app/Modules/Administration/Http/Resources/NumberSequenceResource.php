<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Resources;

use App\Modules\Administration\Models\NumberSequence;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin NumberSequence
 */
class NumberSequenceResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'label' => $this->label,
            'initial_number' => $this->initial_number,
            'current_number' => $this->current_number,
            'maximum_number' => $this->maximum_number,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'padding' => $this->padding,
            'increment' => $this->increment,
            'manual_entry_allowed' => (bool) $this->manual_entry_allowed,
            'format' => $this->format,
            'reset_policy' => $this->reset_policy?->value,
            'last_reset_at' => optional($this->last_reset_at)->toIso8601String(),
        ];
    }
}
