<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Resources;

use App\Modules\Finance\Models\FeeMaster;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin FeeMaster
 */
class FeeMasterResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'fee_category_id' => $this->fee_category_id,
            'category' => $this->whenLoaded('category', fn () => $this->category?->name),
            'academic_year_id' => $this->academic_year_id,
            'class_id' => $this->class_id,
            'class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass?->name),
            'name' => $this->name,
            'amount' => $this->amount,
            'due_date' => $this->due_date?->toDateString(),
            'frequency' => $this->frequency->value,
            'status' => $this->status->value,
        ];
    }
}
