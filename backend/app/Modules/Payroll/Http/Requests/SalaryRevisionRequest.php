<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Http\Requests;

use App\Modules\Payroll\Enums\RevisionType;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class SalaryRevisionRequest extends BaseRequest
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
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'staff_id' => ['required', 'integer', 'exists:staff,id'],
            'structure_id' => ['nullable', 'integer', 'exists:payroll_structures,id'],
            'revision_type' => ['required', Rule::in(RevisionType::values())],
            'effective_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'approved_by' => ['nullable', 'integer', 'exists:staff,id'],
        ];
    }
}
