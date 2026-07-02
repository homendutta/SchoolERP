<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Requests;

use App\Platform\Foundation\Maintenance\Enums\MaintenancePriority;
use App\Platform\Foundation\Maintenance\Enums\MaintenanceStatus;
use App\Platform\Foundation\Maintenance\Enums\MaintenanceType;
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
            'asset_id' => [$required, 'integer', 'exists:assets,id'],
            'type' => ['sometimes', Rule::in(MaintenanceType::values())],
            'priority' => ['sometimes', Rule::in(MaintenancePriority::values())],
            'assigned_staff_id' => ['nullable', 'integer', 'exists:staff,id'],
            'scheduled_date' => ['nullable', 'date'],
            'completed_date' => ['nullable', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(MaintenanceStatus::values())],
        ];
    }
}
