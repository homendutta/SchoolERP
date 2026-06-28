<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Identity\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class RegenerateIdentityRequest extends BaseRequest
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
        return [
            'identity_id' => ['required_without:public_identifier', 'nullable', 'integer', 'exists:identities,id'],
            'public_identifier' => ['required_without:identity_id', 'nullable', 'string'],
        ];
    }
}
