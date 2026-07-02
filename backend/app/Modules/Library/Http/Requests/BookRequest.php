<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class BookRequest extends BaseRequest
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
            'subtitle' => ['nullable', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:32'],
            'edition' => ['nullable', 'string', 'max:50'],
            'language' => ['nullable', 'string', 'max:50'],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:2100'],
            'description' => ['nullable', 'string'],
            'publisher_id' => ['nullable', 'integer', 'exists:library_publishers,id'],
            'category_id' => ['nullable', 'integer', 'exists:library_categories,id'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'author_ids' => ['nullable', 'array'],
            'author_ids.*' => ['integer', 'exists:library_authors,id'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
