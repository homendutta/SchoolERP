<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class ApplicationRequest extends BaseRequest
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
            'academic_year_id' => [$required, 'integer', 'exists:academic_years,id'],
            'class_id' => [$required, 'integer', 'exists:classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'enquiry_id' => ['nullable', 'integer', 'exists:admission_enquiries,id'],

            'student_name' => [$required, 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:100'],

            'guardian_name' => [$required, 'string', 'max:255'],
            'guardian_relation' => ['nullable', 'string', 'max:50'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'guardian_occupation' => ['nullable', 'string', 'max:255'],

            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],

            'previous_school' => ['nullable', 'string', 'max:255'],
            'previous_class' => ['nullable', 'string', 'max:100'],

            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
