<?php

declare(strict_types=1);

namespace App\Modules\Transport\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class RouteRequest extends BaseRequest
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
            'route_code' => ['nullable', 'string', 'max:100'],
            'name' => [$required, 'string', 'max:255'],
            'start_location' => ['nullable', 'string', 'max:255'],
            'end_location' => ['nullable', 'string', 'max:255'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'estimated_minutes' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
