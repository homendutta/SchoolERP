<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class InstallmentRequest extends BaseRequest
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
            'student_fee_id' => [$required, 'integer', 'exists:student_fees,id'],
            'name' => [$required, 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
            'amount' => [$required, 'numeric', 'min:0'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', 'max:30'],
        ];
    }
}
