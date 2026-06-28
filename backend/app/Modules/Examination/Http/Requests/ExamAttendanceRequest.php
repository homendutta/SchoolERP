<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Requests;

use App\Modules\Examination\Enums\ExamAttendanceStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ExamAttendanceRequest extends BaseRequest
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
            'exam_schedule_id' => ['required', 'integer', 'exists:exam_schedules,id'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'entries.*.status' => ['required', Rule::in(ExamAttendanceStatus::values())],
            'entries.*.remarks' => ['nullable', 'string', 'max:500'],
        ];
    }
}
