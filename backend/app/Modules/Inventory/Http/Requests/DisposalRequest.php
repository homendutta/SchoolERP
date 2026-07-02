<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Inventory\Enums\DisposalMethod;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class DisposalRequest extends BaseRequest
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
            'asset_id' => ['required', 'integer', 'exists:assets,id'],
            'method' => ['required', Rule::in(DisposalMethod::values())],
            'reason' => ['nullable', 'string', 'max:1000'],
            'disposal_date' => ['nullable', 'date'],
            'value' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
