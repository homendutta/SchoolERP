<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Requests;

use App\Modules\Cms\Enums\ContentStatus;
use App\Modules\Cms\Enums\NoticePriority;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class NoticeRequest extends BaseRequest
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
            'body' => ['nullable', 'string'],
            'publish_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'priority' => ['sometimes', Rule::in(NoticePriority::values())],
            'featured' => ['nullable', 'boolean'],
            'attachment_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'status' => ['sometimes', Rule::in(ContentStatus::values())],
        ];
    }
}
