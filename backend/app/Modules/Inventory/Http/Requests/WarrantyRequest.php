<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Inventory\Enums\WarrantyStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class WarrantyRequest extends BaseRequest
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
            'asset_id' => [$required, 'integer', 'exists:assets,id'],
            'vendor_id' => ['nullable', 'integer', 'exists:inventory_vendors,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'coverage' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(WarrantyStatus::values())],
        ];
    }
}
