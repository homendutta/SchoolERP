<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class EmployeeDocumentRequest extends BaseRequest
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
            'document_type' => [$required, 'string', 'in:appointment_letter,contract,certificate,identity,qualification,experience'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
