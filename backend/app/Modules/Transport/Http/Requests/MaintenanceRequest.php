<?php

declare(strict_types=1);

namespace App\Modules\Transport\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class MaintenanceRequest extends BaseRequest
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
            'service_type' => ['nullable', 'string', 'max:255'],
            'service_due_date' => ['nullable', 'date'],
            'odometer_due' => ['nullable', 'integer', 'min:0'],
            'last_service_date' => ['nullable', 'date'],
            'reminder_days' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
