<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class WithdrawalRequest extends BaseRequest
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
            'withdraw_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'approved_by' => ['nullable', 'integer', 'exists:users,id'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
