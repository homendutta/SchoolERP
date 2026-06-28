<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Resources;

use App\Modules\Finance\Models\FineRule;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin FineRule
 */
class FineRuleResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'name' => $this->name,
            'fee_category_id' => $this->fee_category_id,
            'category' => $this->whenLoaded('category', fn () => $this->category?->name),
            'mode' => $this->mode->value,
            'amount' => $this->amount,
            'grace_period_days' => $this->grace_period_days,
            'max_fine' => $this->max_fine,
            'status' => $this->status->value,
        ];
    }
}
