<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Resources;

use App\Modules\Finance\Models\FeeCategory;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin FeeCategory
 */
class FeeCategoryResource extends BaseResource
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
            'code' => $this->code,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'status' => $this->status->value,
        ];
    }
}
