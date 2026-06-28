<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use App\Modules\Finance\Enums\DiscountMethod;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class DiscountRequest extends BaseRequest
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
            'method' => ['sometimes', Rule::in(DiscountMethod::values())],
            'value' => [$required, 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
