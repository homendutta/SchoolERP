<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class AssignStudentRequest extends BaseRequest
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
        ];
    }
}
