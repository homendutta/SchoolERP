<?php

declare(strict_types=1);

namespace App\Modules\Transport\Http\Requests;

use App\Modules\Transport\Enums\TripShift;
use App\Modules\Transport\Enums\TripStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class TripRequest extends BaseRequest
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
            'vehicle_id' => [$required, 'integer', 'exists:transport_vehicles,id'],
            'route_id' => [$required, 'integer', 'exists:transport_routes,id'],
            'driver_id' => ['nullable', 'integer', 'exists:staff,id'],
            'attendant_id' => ['nullable', 'integer', 'exists:staff,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'shift' => ['sometimes', Rule::in(TripShift::values())],
            'departure_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'arrival_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'status' => ['sometimes', Rule::in(TripStatus::values())],
        ];
    }
}
