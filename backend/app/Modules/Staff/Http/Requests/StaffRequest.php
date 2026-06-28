<?php

declare(strict_types=1);

namespace App\Modules\Staff\Http\Requests;

use App\Modules\Staff\Enums\EmploymentType;
use App\Modules\Staff\Enums\StaffStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StaffRequest extends BaseRequest
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
        $id = $this->route('id');

        return [
            'school_id' => [$required, 'integer', 'exists:schools,id'],
            // Employee number is editable by an administrator; duplicates are never allowed.
            'employee_number' => ['sometimes', 'nullable', 'string', 'max:20', Rule::unique('staff', 'employee_number')->ignore($id)->whereNull('deleted_at')],

            'name' => [$required, 'string', 'max:255'],
            // Gender & blood group are Master Data (never hardcoded).
            'gender_id' => ['sometimes', 'nullable', 'integer', 'exists:master_data_values,id'],
            'date_of_birth' => ['sometimes', 'nullable', 'date'],
            'marital_status' => ['sometimes', 'nullable', 'string', 'max:30'],
            'blood_group_id' => ['sometimes', 'nullable', 'integer', 'exists:master_data_values,id'],

            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'state' => ['sometimes', 'nullable', 'string', 'max:100'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:20'],

            // Department & Designation are Master Data (never hardcoded).
            'department_id' => ['sometimes', 'nullable', 'integer', 'exists:master_data_values,id'],
            'designation_id' => ['sometimes', 'nullable', 'integer', 'exists:master_data_values,id'],
            'employment_type' => ['sometimes', 'nullable', Rule::enum(EmploymentType::class)],
            'joining_date' => ['sometimes', 'nullable', 'date'],
            'confirmation_date' => ['sometimes', 'nullable', 'date'],
            'reporting_manager_id' => ['sometimes', 'nullable', 'integer', 'exists:staff,id'],
            'is_teaching' => ['sometimes', 'boolean'],

            'status' => ['sometimes', Rule::enum(StaffStatus::class)],
            'photo_media_id' => ['sometimes', 'nullable', 'integer', 'exists:media,id'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
