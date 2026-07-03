<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Requests;

use App\Modules\Cms\Enums\ContentStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class EventRequest extends BaseRequest
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
            'description' => ['nullable', 'string'],
            'event_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'string', 'max:20'],
            'end_time' => ['nullable', 'string', 'max:20'],
            'venue' => ['nullable', 'string', 'max:255'],
            'featured_image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'registration_required' => ['nullable', 'boolean'],
            'status' => ['sometimes', Rule::in(ContentStatus::values())],
        ];
    }
}
