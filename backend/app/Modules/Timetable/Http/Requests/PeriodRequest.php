<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class PeriodRequest extends BaseRequest
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
            'start_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'end_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_break' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
