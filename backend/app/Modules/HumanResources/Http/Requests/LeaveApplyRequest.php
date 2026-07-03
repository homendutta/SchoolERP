<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

/** Apply for leave (processed through the Leave Engine). */
class LeaveApplyRequest extends BaseRequest
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
        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'staff_id' => ['required', 'integer', 'exists:staff,id'],
            'leave_type_id' => ['required', 'integer', 'exists:hr_leave_types,id'],
            'leave_policy_id' => ['nullable', 'integer', 'exists:hr_leave_policies,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'days' => ['nullable', 'numeric', 'min:0.5'],
            'reason' => ['nullable', 'string'],
        ];
    }
}
