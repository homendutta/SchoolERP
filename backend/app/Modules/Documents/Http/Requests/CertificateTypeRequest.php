<?php

declare(strict_types=1);

namespace App\Modules\Documents\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class CertificateTypeRequest extends BaseRequest
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
            'category_id' => ['nullable', 'integer', 'exists:document_categories,id'],
            'name' => [$required, 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'default_template_id' => ['nullable', 'integer', 'exists:document_templates,id'],
            'subject_kind' => ['sometimes', 'in:student,staff,guardian'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
