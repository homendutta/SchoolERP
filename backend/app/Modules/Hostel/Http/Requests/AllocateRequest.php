<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class AllocateRequest extends BaseRequest
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
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'bed_id' => ['required', 'integer', 'exists:hostel_beds,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
        ];
    }
}
