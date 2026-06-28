<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Requests;

use App\Modules\Examination\Enums\InvigilatorRole;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class InvigilatorRequest extends BaseRequest
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
            'staff_id' => [$required, 'integer', 'exists:staff,id'],
            'role' => ['sometimes', Rule::in(InvigilatorRole::values())],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
