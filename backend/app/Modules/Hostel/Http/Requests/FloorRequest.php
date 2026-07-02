<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class FloorRequest extends BaseRequest
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
            'building_id' => [$required, 'integer', 'exists:hostel_buildings,id'],
            'floor_number' => ['sometimes', 'integer'],
            'name' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
