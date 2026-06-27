<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class LoginRequest extends BaseRequest
{
    /** Login is a public action. */
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
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }
}
