<?php

declare(strict_types=1);

namespace App\Modules\Transport\Http\Requests;

use App\Modules\Transport\Enums\VehicleStaffRole;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class VehicleStaffRequest extends BaseRequest
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
            'staff_id' => [$required, 'integer', 'exists:staff,id'],
            'role' => [$required, Rule::in(VehicleStaffRole::values())],
            'status' => ['sometimes', 'string', 'max:30'],
        ];
    }
}
