<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use App\Modules\Finance\Enums\AdjustmentType;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class AdjustmentRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'student_fee_id' => ['nullable', 'integer', 'exists:student_fees,id'],
            'type' => ['required', Rule::in(AdjustmentType::values())],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
