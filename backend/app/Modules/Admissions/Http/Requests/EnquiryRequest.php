<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Requests;

use App\Modules\Admissions\Enums\EnquiryStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class EnquiryRequest extends BaseRequest
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
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'student_name' => [$required, 'string', 'max:255'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'class_interested' => ['nullable', 'string', 'max:100'],
            'source_id' => ['nullable', 'integer', 'exists:master_data_values,id'],
            'status' => ['sometimes', Rule::enum(EnquiryStatus::class)],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'follow_up_date' => ['nullable', 'date'],
        ];
    }
}
