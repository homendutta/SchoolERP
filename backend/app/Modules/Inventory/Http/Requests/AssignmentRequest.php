<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Inventory\Enums\AssignmentTarget;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Shared validation for assign + transfer. Person targets are addressed by
 * Identity Number (resolved via the Platform Identity Service) — never a Staff
 * primary key. Non-person targets use a decoupled target_reference string, so
 * Inventory stays independent of other modules' database keys.
 */
class AssignmentRequest extends BaseRequest
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
        $base = [
            'asset_id' => ['required', 'integer', 'exists:assets,id'],
            'target_type' => ['required', Rule::in(AssignmentTarget::values())],
            'identity_number' => ['nullable', 'string', 'required_if:target_type,staff'],
            'target_reference' => ['nullable', 'string', 'max:255'],
            'target_label' => ['nullable', 'string', 'max:255'],
        ];

        if ($this->route()?->getActionMethod() === 'transfer') {
            $base['transfer_type'] = ['nullable', 'string', 'max:50'];
            $base['reason'] = ['nullable', 'string', 'max:1000'];
        }

        return $base;
    }
}
