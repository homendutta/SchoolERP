<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class ExamSubjectRequest extends BaseRequest
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
            'class_id' => [$required, 'integer', 'exists:classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'subject_id' => [$required, 'integer', 'exists:subjects,id'],
            'subject_type_id' => ['nullable', 'integer', 'exists:master_data_values,id'],
            'is_elective' => ['sometimes', 'boolean'],
            'max_marks' => ['sometimes', 'numeric', 'min:1'],
            'passing_marks' => ['sometimes', 'numeric', 'min:0'],
            'has_components' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
