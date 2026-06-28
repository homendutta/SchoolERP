<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use App\Modules\Finance\Enums\DiscountMethod;
use App\Modules\Finance\Enums\ScholarshipType;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ScholarshipRequest extends BaseRequest
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
            'type' => ['sometimes', Rule::in(ScholarshipType::values())],
            'method' => ['sometimes', Rule::in(DiscountMethod::values())],
            'value' => ['sometimes', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
