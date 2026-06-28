<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class GuardianLinkRequest extends BaseRequest
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
            'guardian_id' => [$required, 'integer', 'exists:guardians,id'],
            // Relationship type is Master Data (never free text).
            'relationship_type_id' => ['sometimes', 'nullable', 'integer', 'exists:master_data_values,id'],
            'is_primary' => ['sometimes', 'boolean'],
            'emergency_contact' => ['sometimes', 'boolean'],
            'pickup_authorized' => ['sometimes', 'boolean'],
            'financial_responsible' => ['sometimes', 'boolean'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
