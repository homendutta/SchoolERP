<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class TermRequest extends BaseRequest
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
            'academic_year_id' => [$required, 'integer', 'exists:academic_years,id'],
            'name' => [$required, 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:50'],
            'start_date' => [$required, 'date'],
            'end_date' => [$required, 'date', 'after:start_date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
