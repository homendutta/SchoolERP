<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Media\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UploadMediaRequest extends BaseRequest
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
        $maxKb = (int) config('media.max_size_kb', 20480);

        return [
            'file' => ['required', 'file', "max:{$maxKb}"],
            'collection' => ['sometimes', 'string', 'max:100'],
            'visibility' => ['sometimes', Rule::in(['public', 'private'])],
            'school_id' => ['sometimes', 'nullable', 'integer', 'exists:schools,id'],
        ];
    }
}
