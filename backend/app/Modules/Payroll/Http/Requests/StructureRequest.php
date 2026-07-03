<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class StructureRequest extends BaseRequest
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
            'grade' => ['nullable', 'string', 'max:50'],
            'effective_date' => ['nullable', 'date'],
            'version' => ['nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
            'components' => ['nullable', 'array'],
            'components.*.component_id' => ['required_with:components', 'integer', 'exists:payroll_components,id'],
            'components.*.value' => ['nullable', 'numeric'],
            'components.*.sequence' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
