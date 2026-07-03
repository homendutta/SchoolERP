<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Requests;

use App\Modules\HumanResources\Enums\ClearanceStatus;
use App\Modules\HumanResources\Enums\SeparationType;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class SeparationRequest extends BaseRequest
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
            'staff_id' => [$required, 'integer', 'exists:staff,id'],
            'separation_type' => [$required, Rule::in(SeparationType::values())],
            'last_working_day' => ['nullable', 'date'],
            'reason' => ['nullable', 'string'],
            'clearance_status' => ['sometimes', Rule::in(ClearanceStatus::values())],
            'exit_notes' => ['nullable', 'string'],
        ];
    }
}
