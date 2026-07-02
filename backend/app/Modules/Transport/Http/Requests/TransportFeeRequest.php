<?php

declare(strict_types=1);

namespace App\Modules\Transport\Http\Requests;

use App\Modules\Transport\Enums\TransportFeeType;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class TransportFeeRequest extends BaseRequest
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
            'fee_type' => ['sometimes', Rule::in(TransportFeeType::values())],
            'route_id' => ['nullable', 'integer', 'exists:transport_routes,id'],
            'stop_id' => ['nullable', 'integer', 'exists:transport_stops,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'name' => [$required, 'string', 'max:255'],
            'amount' => [$required, 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
