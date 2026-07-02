<?php

declare(strict_types=1);

namespace App\Modules\Transport\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class StopRequest extends BaseRequest
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
            'route_id' => [$required, 'integer', 'exists:transport_routes,id'],
            'name' => [$required, 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'sequence' => ['sometimes', 'integer', 'min:0'],
            'pickup_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'drop_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
