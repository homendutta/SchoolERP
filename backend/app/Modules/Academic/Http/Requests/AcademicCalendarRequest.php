<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class AcademicCalendarRequest extends BaseRequest
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
            'academic_year_id' => [$required, 'integer', 'exists:academic_years,id'],
            'name' => [$required, 'string', 'max:255'],
        ];
    }
}
