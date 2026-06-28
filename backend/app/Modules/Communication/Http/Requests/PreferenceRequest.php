<?php

declare(strict_types=1);

namespace App\Modules\Communication\Http\Requests;

use App\Modules\Communication\Enums\CommunicationChannel;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class PreferenceRequest extends BaseRequest
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
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'preferences' => ['required', 'array', 'min:1'],
            'preferences.*.channel' => ['required', Rule::in(CommunicationChannel::values())],
            'preferences.*.is_enabled' => ['required', 'boolean'],
        ];
    }
}
