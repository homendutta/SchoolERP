<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Requests;

use App\Modules\HumanResources\Enums\EmploymentStatus;
use App\Modules\Staff\Enums\EmploymentType;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class EmploymentRequest extends BaseRequest
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
            'department_id' => ['nullable', 'integer', 'exists:hr_departments,id'],
            'designation_id' => ['nullable', 'integer', 'exists:hr_designations,id'],
            'employment_type' => ['nullable', Rule::in(EmploymentType::values())],
            'joining_date' => ['nullable', 'date'],
            'confirmation_date' => ['nullable', 'date'],
            'contract_start' => ['nullable', 'date'],
            'contract_end' => ['nullable', 'date'],
            'reporting_manager_id' => ['nullable', 'integer', 'exists:staff,id'],
            'status' => ['sometimes', Rule::in(EmploymentStatus::values())],
            'change_reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
