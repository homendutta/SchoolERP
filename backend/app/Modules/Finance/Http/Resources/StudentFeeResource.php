<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Resources;

use App\Modules\Finance\Models\StudentFee;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin StudentFee
 */
class StudentFeeResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'student_id' => $this->student_id,
            'student' => $this->whenLoaded('student', fn () => $this->student?->name),
            'admission_number' => $this->whenLoaded('student', fn () => $this->student?->admission_number),
            'fee_structure_id' => $this->fee_structure_id,
            'academic_year_id' => $this->academic_year_id,
            'class_id' => $this->class_id,
            'class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass?->name),
            'section_id' => $this->section_id,
            'total_amount' => $this->total_amount,
            'discount_amount' => $this->discount_amount,
            'scholarship_amount' => $this->scholarship_amount,
            'net_amount' => $this->net_amount,
            'paid_amount' => $this->paid_amount,
            'status' => $this->status->value,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($i) => [
                'id' => $i->id,
                'name' => $i->name,
                'amount' => $i->amount,
                'paid_amount' => $i->paid_amount,
                'due_date' => $i->due_date?->toDateString(),
                'status' => $i->status->value,
                'fee_category_id' => $i->fee_category_id,
            ])->values()),
        ];
    }
}
