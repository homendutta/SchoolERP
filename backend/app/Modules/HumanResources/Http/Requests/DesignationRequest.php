<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class DesignationRequest extends BaseRequest
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
            'department_id' => ['nullable', 'integer', 'exists:hr_departments,id'],
            'parent_id' => ['nullable', 'integer', 'exists:hr_designations,id'],
            'name' => [$required, 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'grade' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
