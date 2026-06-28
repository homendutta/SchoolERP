<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Resources;

use App\Modules\Finance\Models\Adjustment;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin Adjustment
 */
class AdjustmentResource extends BaseResource
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
            'student_fee_id' => $this->student_fee_id,
            'transaction_number' => $this->transaction_number,
            'type' => $this->type->value,
            'amount' => $this->amount,
            'reason' => $this->reason,
            'status' => $this->status,
        ];
    }
}
