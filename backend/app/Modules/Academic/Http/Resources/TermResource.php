<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Resources;

use App\Modules\Academic\Models\Term;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin Term
 */
class TermResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'sort_order' => $this->sort_order,
            'status' => $this->status?->value,
            'archived' => $this->trashed(),
        ];
    }
}
