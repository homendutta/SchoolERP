<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class SectionRequest extends BaseRequest
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
            'class_id' => [$required, 'integer', 'exists:classes,id'],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'name' => [$required, 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
