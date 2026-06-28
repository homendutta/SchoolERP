<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Resources;

use App\Modules\Students\Models\StudentWithdrawal;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin StudentWithdrawal
 */
class WithdrawalResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'withdraw_date' => $this->withdraw_date?->toDateString(),
            'reason' => $this->reason,
            'approved_by' => $this->approved_by,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
