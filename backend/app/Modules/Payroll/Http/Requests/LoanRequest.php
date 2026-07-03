<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Http\Requests;

use App\Modules\Payroll\Enums\LoanType;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class LoanRequest extends BaseRequest
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
        $required = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'school_id' => [$required, 'integer', 'exists:schools,id'],
            'staff_id' => [$required, 'integer', 'exists:staff,id'],
            'loan_type' => ['sometimes', Rule::in(LoanType::values())],
            'reference' => ['nullable', 'string', 'max:100'],
            'principal' => [$required, 'numeric', 'min:0'],
            'balance' => ['nullable', 'numeric', 'min:0'],
            'installment_amount' => ['nullable', 'numeric', 'min:0'],
            'disbursed_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
