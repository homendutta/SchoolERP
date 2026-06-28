<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class ExamScheduleRequest extends BaseRequest
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
            'exam_session_id' => [$required, 'integer', 'exists:exam_sessions,id'],
            'exam_subject_id' => [$required, 'integer', 'exists:exam_subjects,id'],
            'exam_date' => [$required, 'date'],
            'period_id' => ['nullable', 'integer', 'exists:timetable_periods,id'],
            'start_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'duration_minutes' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', 'string', 'max:30'],
        ];
    }
}
