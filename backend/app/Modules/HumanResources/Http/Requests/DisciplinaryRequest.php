<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Requests;

use App\Modules\HumanResources\Enums\DisciplinaryAction;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class DisciplinaryRequest extends BaseRequest
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
            'action_type' => [$required, Rule::in(DisciplinaryAction::values())],
            'incident_date' => ['nullable', 'date'],
            'action_date' => ['nullable', 'date'],
            'subject' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
            'status' => ['sometimes', 'string', 'max:50'],
        ];
    }
}
