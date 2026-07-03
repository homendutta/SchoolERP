<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Requests;

use App\Modules\HumanResources\Enums\HolidayType;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class HolidayRequest extends BaseRequest
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
            'date' => [$required, 'date'],
            'end_date' => ['nullable', 'date'],
            'holiday_type' => ['sometimes', Rule::in(HolidayType::values())],
            'is_optional' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
