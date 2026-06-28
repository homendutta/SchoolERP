<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class CopyTimetableRequest extends BaseRequest
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
            'from_academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'to_academic_year_id' => ['required', 'integer', 'different:from_academic_year_id', 'exists:academic_years,id'],
            'from_template_id' => ['nullable', 'integer', 'exists:timetable_templates,id'],
            'to_template_id' => ['nullable', 'integer', 'exists:timetable_templates,id'],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['integer', 'exists:classes,id'],
        ];
    }
}
