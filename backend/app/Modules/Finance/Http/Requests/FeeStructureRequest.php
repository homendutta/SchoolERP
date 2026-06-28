<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class FeeStructureRequest extends BaseRequest
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
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'name' => [$required, 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
            'items' => ['sometimes', 'array'],
            'items.*.fee_master_id' => ['required_with:items', 'integer', 'exists:fee_masters,id'],
            'items.*.amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
