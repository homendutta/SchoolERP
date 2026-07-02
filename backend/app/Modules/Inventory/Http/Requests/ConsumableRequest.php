<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class ConsumableRequest extends BaseRequest
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
            'category_id' => ['nullable', 'integer', 'exists:asset_categories,id'],
            'name' => [$required, 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'unit' => ['sometimes', 'string', 'max:30'],
            'minimum_stock' => ['sometimes', 'numeric', 'min:0'],
            'current_stock' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
