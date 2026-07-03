<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Requests;

use App\Modules\Cms\Enums\ContentStatus;
use App\Modules\Cms\Enums\VideoProvider;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class VideoRequest extends BaseRequest
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
            'description' => ['nullable', 'string'],
            'provider' => ['sometimes', Rule::in(VideoProvider::values())],
            'video_url' => ['nullable', 'string', 'max:500'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
            'thumbnail_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'featured' => ['nullable', 'boolean'],
            'status' => ['sometimes', Rule::in(ContentStatus::values())],
        ];
    }
}
