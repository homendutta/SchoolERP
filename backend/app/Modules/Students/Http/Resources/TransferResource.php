<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Resources;

use App\Modules\Students\Models\StudentTransfer;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin StudentTransfer
 */
class TransferResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'type' => $this->type?->value,
            'academic_year_id' => $this->academic_year_id,
            'from_class_id' => $this->from_class_id,
            'from_section_id' => $this->from_section_id,
            'to_class_id' => $this->to_class_id,
            'to_section_id' => $this->to_section_id,
            'transfer_date' => $this->transfer_date?->toDateString(),
            'reason' => $this->reason,
            'destination_school' => $this->destination_school,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
