<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Requests;

use App\Modules\Hostel\Enums\HostelFeeType;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class HostelFeeRequest extends BaseRequest
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
            'hostel_id' => ['nullable', 'integer', 'exists:hostels,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'fee_type' => ['sometimes', Rule::in(HostelFeeType::values())],
            'name' => [$required, 'string', 'max:255'],
            'amount' => [$required, 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
