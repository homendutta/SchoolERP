<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class DeviceRequest extends BaseRequest
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
            'device_type' => ['sometimes', 'string', 'max:50'],
            'device_identifier' => [$required, 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:30'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
