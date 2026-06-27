<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class RoomRequest extends BaseRequest
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
            'room_type_id' => ['nullable', 'integer', 'exists:master_data_values,id'],
            'code' => [$required, 'string', 'max:50'],
            'name' => [$required, 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'building' => ['nullable', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
