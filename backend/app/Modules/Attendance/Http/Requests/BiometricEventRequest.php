<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

/**
 * Accepts either a single normalised event (identity_number + event_time) or a
 * raw vendor payload (vendor + payload) that a connector will normalise.
 */
class BiometricEventRequest extends BaseRequest
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
            'device_identifier' => ['nullable', 'string'],

            // Normalised single event
            'identity_number' => ['required_without:payload', 'nullable', 'string'],
            'event_time' => ['required_without:payload', 'nullable', 'date'],
            'direction' => ['nullable', 'in:in,out'],

            // Raw vendor payload (parsed by the connector)
            'vendor' => ['required_with:payload', 'nullable', 'string'],
            'payload' => ['nullable', 'array'],
        ];
    }
}
