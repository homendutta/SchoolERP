<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class DocumentRequest extends BaseRequest
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
            'application_id' => [$required, 'integer', 'exists:admission_applications,id'],
            // Document type is Master Data (never hardcoded).
            'document_type_id' => ['nullable', 'integer', 'exists:master_data_values,id'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
