<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use App\Modules\Finance\Enums\FeeFrequency;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class FeeMasterRequest extends BaseRequest
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
            'fee_category_id' => [$required, 'integer', 'exists:fee_categories,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'name' => [$required, 'string', 'max:255'],
            'amount' => [$required, 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'frequency' => ['sometimes', Rule::in(FeeFrequency::values())],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
