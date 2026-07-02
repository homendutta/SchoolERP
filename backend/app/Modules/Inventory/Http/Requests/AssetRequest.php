<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Inventory\Enums\AssetCondition;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class AssetRequest extends BaseRequest
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
            'asset_number' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'asset_model_id' => ['nullable', 'integer', 'exists:asset_models,id'],
            'category_id' => ['nullable', 'integer', 'exists:asset_categories,id'],
            'vendor_id' => ['nullable', 'integer', 'exists:inventory_vendors,id'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_value' => ['nullable', 'numeric', 'min:0'],
            'current_value' => ['nullable', 'numeric', 'min:0'],
            'condition' => ['sometimes', Rule::in(AssetCondition::values())],
            // `status` is intentionally NOT accepted here — the asset lifecycle state
            // only changes through the lifecycle endpoint (POST assets/{id}/lifecycle),
            // so every transition is written to the Audit Log and the asset Timeline.
            'photo_media_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
