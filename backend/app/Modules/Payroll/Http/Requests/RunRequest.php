<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class RunRequest extends BaseRequest
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
            'label' => ['nullable', 'string', 'max:255'],
            'period_year' => [$required, 'integer', 'min:2000', 'max:2100'],
            'period_month' => [$required, 'integer', 'min:1', 'max:12'],
        ];
    }
}
