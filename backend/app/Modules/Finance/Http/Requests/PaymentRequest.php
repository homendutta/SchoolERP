<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class PaymentRequest extends BaseRequest
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method_id' => ['nullable', 'integer', 'exists:master_data_values,id'],
            'paid_on' => ['nullable', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'gateway' => ['nullable', 'string', 'max:50'],
            'allocations' => ['sometimes', 'array'],
            'allocations.*.student_fee_item_id' => ['required_with:allocations', 'integer', 'exists:student_fee_items,id'],
            'allocations.*.amount' => ['required_with:allocations', 'numeric', 'min:0'],
        ];
    }
}
