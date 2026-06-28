<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use App\Modules\Finance\Enums\FineMode;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class FineRuleRequest extends BaseRequest
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
            'fee_category_id' => ['nullable', 'integer', 'exists:fee_categories,id'],
            'mode' => ['sometimes', Rule::in(FineMode::values())],
            'amount' => [$required, 'numeric', 'min:0'],
            'grace_period_days' => ['sometimes', 'integer', 'min:0'],
            'max_fine' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
