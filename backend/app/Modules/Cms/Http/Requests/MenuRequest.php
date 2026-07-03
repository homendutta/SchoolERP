<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Requests;

use App\Modules\Cms\Enums\MenuLocation;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class MenuRequest extends BaseRequest
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
            'location' => [$required, Rule::in(MenuLocation::values())],
            'label' => [$required, 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:500'],
            'parent_id' => ['nullable', 'integer', 'exists:cms_menus,id'],
            'sequence' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
