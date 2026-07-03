<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Requests;

use App\Modules\Cms\Enums\ContentStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class PageRequest extends BaseRequest
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
            'title' => [$required, 'string', 'max:255'],
            'slug' => [$required, 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'template' => ['nullable', 'string', 'max:100'],
            'seo' => ['nullable', 'array'],
            'status' => ['sometimes', Rule::in(ContentStatus::values())],
        ];
    }
}
