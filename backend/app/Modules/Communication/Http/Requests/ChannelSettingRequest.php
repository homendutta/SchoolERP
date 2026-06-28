<?php

declare(strict_types=1);

namespace App\Modules\Communication\Http\Requests;

use App\Modules\Communication\Enums\BackoffStrategy;
use App\Modules\Communication\Enums\CommunicationChannel;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ChannelSettingRequest extends BaseRequest
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
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'channel' => ['required', Rule::in(CommunicationChannel::values())],
            'is_enabled' => ['sometimes', 'boolean'],
            'provider' => ['nullable', 'string', 'max:100'],
            'config' => ['nullable', 'array'],
            'max_attempts' => ['sometimes', 'integer', 'min:1', 'max:20'],
            'retry_delay_seconds' => ['sometimes', 'integer', 'min:0'],
            'backoff' => ['sometimes', Rule::in(BackoffStrategy::values())],
        ];
    }
}
