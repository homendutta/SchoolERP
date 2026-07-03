<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class LeavePolicyRequest extends BaseRequest
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
            'leave_type_id' => [$required, 'integer', 'exists:hr_leave_types,id'],
            'name' => [$required, 'string', 'max:255'],
            'annual_allocation' => ['nullable', 'numeric', 'min:0'],
            'carry_forward' => ['nullable', 'boolean'],
            'carry_forward_limit' => ['nullable', 'numeric', 'min:0'],
            'encashment_allowed' => ['nullable', 'boolean'],
            'negative_balance_allowed' => ['nullable', 'boolean'],
            'approval_levels' => ['nullable', 'integer', 'min:1', 'max:5'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
