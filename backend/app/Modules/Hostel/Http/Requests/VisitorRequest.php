<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Requests;

use App\Modules\Hostel\Enums\VisitorStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class VisitorRequest extends BaseRequest
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
            'hostel_id' => ['nullable', 'integer', 'exists:hostels,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'visitor_name' => [$required, 'string', 'max:255'],
            'identity_proof' => ['nullable', 'string', 'max:255'],
            'id_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'visit_date' => ['nullable', 'date'],
            'check_in' => ['nullable', 'date'],
            'check_out' => ['nullable', 'date'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(VisitorStatus::values())],
        ];
    }
}
