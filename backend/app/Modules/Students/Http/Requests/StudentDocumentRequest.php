<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class StudentDocumentRequest extends BaseRequest
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
            'student_id' => [$required, 'integer', 'exists:students,id'],
            // Document type is Master Data; the file is a Media reference only.
            'document_type_id' => ['nullable', 'integer', 'exists:master_data_values,id'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
