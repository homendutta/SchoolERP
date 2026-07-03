<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class SalaryAssignmentRequest extends BaseRequest
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
            'structure_id' => [$required, 'integer', 'exists:payroll_structures,id'],
            'effective_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'approved_by' => ['nullable', 'integer', 'exists:staff,id'],
        ];
    }
}
