<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Requests;

use App\Modules\Cms\Enums\CategoryType;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends BaseRequest
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
            'type' => [$required, Rule::in(CategoryType::values())],
            'name' => [$required, 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
