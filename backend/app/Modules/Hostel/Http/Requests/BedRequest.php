<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Requests;

use App\Modules\Hostel\Enums\BedStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class BedRequest extends BaseRequest
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
            'room_id' => [$required, 'integer', 'exists:hostel_rooms,id'],
            'bed_number' => [$required, 'string', 'max:50'],
            'bed_code' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', Rule::in(BedStatus::values())],
        ];
    }
}
