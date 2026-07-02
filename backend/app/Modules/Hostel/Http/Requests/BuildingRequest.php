<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class BuildingRequest extends BaseRequest
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
            'hostel_id' => [$required, 'integer', 'exists:hostels,id'],
            'name' => [$required, 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'floors_count' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
