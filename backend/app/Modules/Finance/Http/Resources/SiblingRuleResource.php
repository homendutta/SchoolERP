<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Resources;

use App\Modules\Finance\Models\SiblingDiscountRule;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin SiblingDiscountRule
 */
class SiblingRuleResource extends BaseResource
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
            'child_order' => $this->child_order,
            'method' => $this->method->value,
            'value' => $this->value,
            'status' => $this->status->value,
        ];
    }
}
