<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Http\Requests;

use App\Modules\Integrations\Enums\WebhookDirection;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class WebhookRequest extends BaseRequest
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
            'name' => [$required, 'string', 'max:255'],
            'direction' => [$required, Rule::in(WebhookDirection::values())],
            'url' => ['nullable', 'string', 'max:500'],
            'secret' => ['nullable', 'string', 'max:255'],
            'events' => ['nullable', 'array'],
            'max_retries' => ['nullable', 'integer', 'min:0', 'max:10'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
