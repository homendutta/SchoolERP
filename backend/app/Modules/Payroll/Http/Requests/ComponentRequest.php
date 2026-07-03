<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Http\Requests;

use App\Modules\Payroll\Enums\CalculationType;
use App\Modules\Payroll\Enums\ComponentType;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ComponentRequest extends BaseRequest
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
            'code' => ['nullable', 'string', 'max:50'],
            'component_type' => [$required, Rule::in(ComponentType::values())],
            'calculation_type' => ['sometimes', Rule::in(CalculationType::values())],
            'default_value' => ['nullable', 'numeric'],
            'based_on' => ['nullable', 'string', 'max:50'],
            'formula' => ['nullable', 'string'],
            'taxable' => ['nullable', 'boolean'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
