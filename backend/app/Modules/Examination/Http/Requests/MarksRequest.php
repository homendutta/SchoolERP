<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class MarksRequest extends BaseRequest
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
            'exam_subject_id' => ['required', 'integer', 'exists:exam_subjects,id'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'entries.*.component_id' => ['nullable', 'integer', 'exists:exam_components,id'],
            'entries.*.marks_obtained' => ['nullable', 'numeric', 'min:0'],
            'entries.*.is_absent' => ['sometimes', 'boolean'],
            'entries.*.remarks' => ['nullable', 'string', 'max:500'],
        ];
    }
}
