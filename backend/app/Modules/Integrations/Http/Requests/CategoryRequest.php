<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class CategoryRequest extends BaseRequest
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
            'name' => [$required, 'string', 'max:255'],
            'code' => [$required, 'string', 'max:100'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
