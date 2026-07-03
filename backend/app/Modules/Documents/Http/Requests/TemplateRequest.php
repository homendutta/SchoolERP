<?php

declare(strict_types=1);

namespace App\Modules\Documents\Http\Requests;

use App\Modules\Documents\Enums\Orientation;
use App\Modules\Documents\Enums\PaperSize;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class TemplateRequest extends BaseRequest
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
            'certificate_type_id' => ['nullable', 'integer', 'exists:document_certificate_types,id'],
            'name' => [$required, 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'html' => ['nullable', 'string'],
            'header' => ['nullable', 'string'],
            'footer' => ['nullable', 'string'],
            'variables' => ['nullable', 'array'],
            'logo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'watermark_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'background_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'margins' => ['nullable', 'array'],
            'orientation' => ['sometimes', Rule::in(Orientation::values())],
            'paper_size' => ['sometimes', Rule::in(PaperSize::values())],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
