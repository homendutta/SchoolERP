<?php

declare(strict_types=1);

namespace App\Modules\Staff\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class QualificationRequest extends BaseRequest
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
            'qualification' => [$required, 'string', 'max:255'],
            'institution' => ['nullable', 'string', 'max:255'],
            'board_university' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'string', 'max:10'],
            'grade' => ['nullable', 'string', 'max:50'],
            'certificate_media_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
