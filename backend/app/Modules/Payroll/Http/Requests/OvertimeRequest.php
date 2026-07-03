<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class OvertimeRequest extends BaseRequest
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
            'period_year' => [$required, 'integer', 'min:2000', 'max:2100'],
            'period_month' => [$required, 'integer', 'min:1', 'max:12'],
            'hours' => ['nullable', 'numeric', 'min:0'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'max_hours' => ['nullable', 'numeric', 'min:0'],
            'eligible' => ['nullable', 'boolean'],
            'approved' => ['nullable', 'boolean'],
            'approved_by' => ['nullable', 'integer', 'exists:staff,id'],
            'status' => ['nullable', 'string', 'max:50'],
        ];
    }
}
