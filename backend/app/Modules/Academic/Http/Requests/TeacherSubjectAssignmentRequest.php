<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class TeacherSubjectAssignmentRequest extends BaseRequest
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
            'academic_year_id' => [$required, 'integer', 'exists:academic_years,id'],
            'class_id' => [$required, 'integer', 'exists:classes,id'],
            'section_id' => [$required, 'integer', 'exists:sections,id'],
            'subject_id' => [$required, 'integer', 'exists:subjects,id'],
            'teacher_id' => [$required, 'integer', 'exists:users,id'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
