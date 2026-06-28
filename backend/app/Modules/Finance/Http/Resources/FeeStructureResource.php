<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Resources;

use App\Modules\Finance\Models\FeeStructure;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin FeeStructure
 */
class FeeStructureResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'academic_year_id' => $this->academic_year_id,
            'class_id' => $this->class_id,
            'class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass?->name),
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'status' => $this->status->value,
            'items_count' => $this->whenCounted('items'),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($i) => [
                'id' => $i->id,
                'fee_master_id' => $i->fee_master_id,
                'fee_master' => $i->feeMaster?->name,
                'amount' => $i->amount ?? $i->feeMaster?->amount,
            ])->values()),
        ];
    }
}
