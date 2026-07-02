<?php

declare(strict_types=1);

namespace App\Modules\Transport\Http\Requests;

use App\Modules\Transport\Enums\FuelType;
use App\Modules\Transport\Enums\VehicleStatus;
use App\Modules\Transport\Enums\VehicleType;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends BaseRequest
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
            'vehicle_number' => ['nullable', 'string', 'max:100'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'vehicle_type' => ['sometimes', Rule::in(VehicleType::values())],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1950', 'max:2100'],
            'seating_capacity' => [$required, 'integer', 'min:0'],
            'reserved_seats' => ['sometimes', 'integer', 'min:0'],
            'fuel_type' => ['sometimes', Rule::in(FuelType::values())],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'photo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'status' => ['sometimes', Rule::in(VehicleStatus::values())],
        ];
    }
}
