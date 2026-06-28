<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Resources;

use App\Modules\Finance\Models\Refund;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin Refund
 */
class RefundResource extends BaseResource
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
            'payment_id' => $this->payment_id,
            'receipt_number' => $this->whenLoaded('payment', fn () => $this->payment?->receipt_number),
            'transaction_number' => $this->transaction_number,
            'amount' => $this->amount,
            'type' => $this->type->value,
            'reason' => $this->reason,
            'refunded_on' => $this->refunded_on?->toDateString(),
            'status' => $this->status,
        ];
    }
}
