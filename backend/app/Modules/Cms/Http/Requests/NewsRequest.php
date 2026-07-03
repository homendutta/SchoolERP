<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Requests;

use App\Modules\Cms\Enums\ContentStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class NewsRequest extends BaseRequest
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
            'slug' => [$required, 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'featured_image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'gallery' => ['nullable', 'array'],
            'seo' => ['nullable', 'array'],
            'publish_date' => ['nullable', 'date'],
            'featured' => ['nullable', 'boolean'],
            'status' => ['sometimes', Rule::in(ContentStatus::values())],
            'scheduled_at' => ['nullable', 'date'],
        ];
    }
}
