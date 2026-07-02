<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Requests;

use App\Modules\Hostel\Enums\WardenRole;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class WardenRequest extends BaseRequest
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
            'staff_id' => [$required, 'integer', 'exists:staff,id'],
            'role' => ['sometimes', Rule::in(WardenRole::values())],
            'assigned_on' => ['nullable', 'date'],
            'ended_on' => ['nullable', 'date'],
            'status' => ['sometimes', 'string', 'max:30'],
        ];
    }
}
