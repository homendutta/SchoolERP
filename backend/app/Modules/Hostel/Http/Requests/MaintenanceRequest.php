<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Requests;

use App\Modules\Hostel\Enums\MaintenanceCategory;
use App\Modules\Hostel\Enums\MaintenancePriority;
use App\Modules\Hostel\Enums\MaintenanceStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

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
            'hostel_id' => ['nullable', 'integer', 'exists:hostels,id'],
            'room_id' => ['nullable', 'integer', 'exists:hostel_rooms,id'],
            'reported_by' => ['nullable', 'integer'],
            'category' => ['sometimes', Rule::in(MaintenanceCategory::values())],
            'priority' => ['sometimes', Rule::in(MaintenancePriority::values())],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(MaintenanceStatus::values())],
            'assigned_staff_id' => ['nullable', 'integer', 'exists:staff,id'],
            'resolution_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
