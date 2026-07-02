<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class LocationRequest extends BaseRequest
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
            'room' => ['nullable', 'string', 'max:100'],
            'rack' => ['nullable', 'string', 'max:100'],
            'shelf' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
