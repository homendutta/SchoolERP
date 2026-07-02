<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class VendorRequest extends BaseRequest
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
            'name' => [$required, 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
