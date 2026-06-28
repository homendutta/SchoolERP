<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class StudentMedicalRequest extends BaseRequest
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
            // Blood group is Master Data (never hardcoded).
            'blood_group_id' => ['sometimes', 'nullable', 'integer', 'exists:master_data_values,id'],
            'allergies' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'disabilities' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'medical_notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'emergency_instructions' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
