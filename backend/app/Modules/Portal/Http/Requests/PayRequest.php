<?php

declare(strict_types=1);

namespace App\Modules\Portal\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

/**
 * Online fee payment. A parent may include multiple children; a student includes
 * only their own. Per-student authorization is enforced in the service.
 */
class PayRequest extends BaseRequest
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
            'gateway' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'items.*.amount' => ['required', 'numeric', 'min:0.01'],
            'items.*.reference' => ['nullable', 'string', 'max:100'],
            'items.*.allocations' => ['nullable', 'array'],
            'items.*.allocations.*.student_fee_item_id' => ['required_with:items.*.allocations', 'integer'],
            'items.*.allocations.*.amount' => ['required_with:items.*.allocations', 'numeric', 'min:0.01'],
        ];
    }
}
