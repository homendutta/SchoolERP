<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Http\Requests;

use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ManualAttendanceRequest extends BaseRequest
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
            'date' => ['required', 'date'],
            'session_id' => ['nullable', 'integer', 'exists:master_data_values,id'],
            'shift' => ['nullable', 'string', 'max:50'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.identity_id' => ['required_without:entries.*.identity_number', 'nullable', 'integer', 'exists:identities,id'],
            'entries.*.identity_number' => ['required_without:entries.*.identity_id', 'nullable', 'string'],
            'entries.*.status' => ['required', Rule::enum(AttendanceStatus::class)],
            'entries.*.is_late' => ['sometimes', 'boolean'],
            'entries.*.remarks' => ['nullable', 'string', 'max:500'],
        ];
    }
}
