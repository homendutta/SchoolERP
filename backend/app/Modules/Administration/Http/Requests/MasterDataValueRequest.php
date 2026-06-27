<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class MasterDataValueRequest extends BaseRequest
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
            'type_id' => [$required, 'integer', 'exists:master_data_types,id'],
            'label' => [$required, 'string', 'max:255'],
            'value' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
