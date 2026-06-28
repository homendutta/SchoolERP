<?php

declare(strict_types=1);

namespace App\Modules\Communication\Http\Requests;

use App\Modules\Communication\Enums\CommunicationChannel;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class TemplateRequest extends BaseRequest
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
            'code' => [$required, 'string', 'max:100'],
            'channel' => ['sometimes', Rule::in(CommunicationChannel::values())],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => [$required, 'string'],
            'variables' => ['nullable', 'array'],
            'language' => ['sometimes', 'string', 'max:10'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
