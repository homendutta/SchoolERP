<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class AssignFeeRequest extends BaseRequest
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
            'structure_id' => ['required', 'integer', 'exists:fee_structures,id'],
            // Individual assignment
            'student_id' => ['required_without:bulk', 'integer', 'exists:students,id'],
            // Bulk assignment (class / section / explicit list)
            'bulk' => ['sometimes', 'boolean'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ];
    }
}
