<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Requests;

use App\Modules\Timetable\Enums\SubstitutionStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class SubstitutionRequest extends BaseRequest
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
            'class_timetable_id' => ['nullable', 'integer', 'exists:class_timetables,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'original_teacher_id' => ['nullable', 'integer', 'exists:staff,id'],
            'substitute_teacher_id' => [$required, 'integer', 'exists:staff,id'],
            'date' => [$required, 'date'],
            'period_id' => [$required, 'integer', 'exists:timetable_periods,id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', Rule::in(SubstitutionStatus::values())],
        ];
    }
}
