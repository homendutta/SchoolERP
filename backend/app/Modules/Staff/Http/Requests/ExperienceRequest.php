<?php

declare(strict_types=1);

namespace App\Modules\Staff\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class ExperienceRequest extends BaseRequest
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
            'staff_id' => [$required, 'integer', 'exists:staff,id'],
            'organization' => [$required, 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'reason_for_leaving' => ['nullable', 'string', 'max:255'],
            'certificate_media_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
