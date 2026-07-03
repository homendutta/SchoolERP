<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class AttendancePolicyRequest extends BaseRequest
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
            'name' => [$required, 'string', 'max:255'],
            'grace_minutes' => ['nullable', 'integer', 'min:0'],
            'half_day_hours' => ['nullable', 'numeric', 'min:0'],
            'late_after_minutes' => ['nullable', 'integer', 'min:0'],
            'overtime_eligible' => ['nullable', 'boolean'],
            'minimum_working_hours' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
