<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Resources;

use App\Modules\Finance\Models\FeeInstallment;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin FeeInstallment
 */
class InstallmentResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'student_fee_id' => $this->student_fee_id,
            'name' => $this->name,
            'due_date' => $this->due_date?->toDateString(),
            'amount' => $this->amount,
            'paid_amount' => $this->paid_amount,
            'status' => $this->status->value,
            'sort_order' => $this->sort_order,
        ];
    }
}
