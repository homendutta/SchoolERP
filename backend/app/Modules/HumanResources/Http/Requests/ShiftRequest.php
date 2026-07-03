<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class ShiftRequest extends BaseRequest
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
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'grace_minutes' => ['nullable', 'integer', 'min:0'],
            'weekly_off_pattern' => ['nullable', 'array'],
            'weekly_off_pattern.*' => ['integer', 'min:0', 'max:6'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
