<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Requests;

use App\Platform\Enums\ResetPolicy;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class NumberSequenceRequest extends BaseRequest
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
            'label' => ['nullable', 'string', 'max:255'],
            'initial_number' => ['sometimes', 'integer', 'min:0'],
            'maximum_number' => ['nullable', 'integer', 'min:0'],
            'prefix' => ['nullable', 'string', 'max:32'],
            'suffix' => ['nullable', 'string', 'max:32'],
            'padding' => ['sometimes', 'integer', 'min:0', 'max:20'],
            'increment' => ['sometimes', 'integer', 'min:1'],
            'manual_entry_allowed' => ['sometimes', 'boolean'],
            'format' => ['sometimes', 'string', 'max:64'],
            'reset_policy' => ['sometimes', Rule::in(ResetPolicy::values())],
        ];
    }
}
