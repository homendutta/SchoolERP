<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Http\Requests;

use App\Modules\Integrations\Enums\ProviderStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ProviderRequest extends BaseRequest
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
            'category_id' => ['nullable', 'integer', 'exists:integration_categories,id'],
            'name' => [$required, 'string', 'max:255'],
            'code' => [$required, 'string', 'max:100'],
            'adapter' => ['nullable', 'string', 'max:100'],
            'version' => ['nullable', 'string', 'max:20'],
            'status' => ['sometimes', Rule::in(ProviderStatus::values())],
            'config' => ['nullable', 'array'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
