<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class SeatingRequest extends BaseRequest
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
            'exam_schedule_id' => [$required, 'integer', 'exists:exam_schedules,id'],
            'room_id' => [$required, 'integer', 'exists:rooms,id'],
            'student_id' => [$required, 'integer', 'exists:students,id'],
            'seat_number' => ['nullable', 'string', 'max:30'],
            'roll_number' => ['nullable', 'string', 'max:30'],
        ];
    }
}
