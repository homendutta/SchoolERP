<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Requests;

use App\Modules\Hostel\Enums\BedStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

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
            'hostel_id' => [$required, 'integer', 'exists:hostels,id'],
            'building_id' => [$required, 'integer', 'exists:hostel_buildings,id'],
            'floor_id' => [$required, 'integer', 'exists:hostel_floors,id'],
            'room_number' => [$required, 'string', 'max:50'],
            'room_type_id' => ['nullable', 'integer', 'exists:master_data_values,id'],
            'capacity' => [$required, 'integer', 'min:1'],
            'photo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'status' => ['sometimes', Rule::in(BedStatus::values())],
        ];
    }
}
