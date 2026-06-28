<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Resources;

use App\Modules\Finance\Models\Payment;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin Payment
 */
class PaymentResource extends BaseResource
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
            'receipt_number' => $this->receipt_number,
            'transaction_number' => $this->transaction_number,
            'payment_method_id' => $this->payment_method_id,
            'payment_method' => $this->whenLoaded('method', fn () => $this->method?->label),
            'amount' => $this->amount,
            'refunded_amount' => $this->refunded_amount,
            'paid_on' => $this->paid_on?->toDateString(),
            'reference' => $this->reference,
            'gateway' => $this->gateway,
            'status' => $this->status->value,
            'allocations' => $this->whenLoaded('allocations', fn () => $this->allocations->map(fn ($a) => [
                'student_fee_item_id' => $a->student_fee_item_id,
                'amount' => $a->amount,
            ])->values()),
        ];
    }
}
