<?php

declare(strict_types=1);

namespace App\Modules\Transport\Http\Requests;

use App\Modules\Transport\Enums\DocumentType;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class VehicleDocumentRequest extends BaseRequest
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
            'vehicle_id' => [$required, 'integer', 'exists:transport_vehicles,id'],
            'document_type' => [$required, Rule::in(DocumentType::values())],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
            'number' => ['nullable', 'string', 'max:100'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
