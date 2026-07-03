<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Requests;

use App\Modules\Cms\Enums\ContentStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class GalleryRequest extends BaseRequest
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
            'category_id' => ['nullable', 'integer', 'exists:cms_categories,id'],
            'title' => [$required, 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'featured' => ['nullable', 'boolean'],
            'status' => ['sometimes', Rule::in(ContentStatus::values())],
            'images' => ['nullable', 'array'],
            'images.*.media_id' => ['required_with:images', 'integer', 'exists:media,id'],
            'images.*.caption' => ['nullable', 'string', 'max:255'],
            'images.*.sequence' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
