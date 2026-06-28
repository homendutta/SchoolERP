<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use App\Modules\Finance\Enums\RefundType;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class RefundRequest extends BaseRequest
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
        return [
            'payment_id' => ['required', 'integer', 'exists:payments,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'type' => ['sometimes', Rule::in(RefundType::values())],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
