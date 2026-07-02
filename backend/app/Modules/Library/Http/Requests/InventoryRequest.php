<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Requests;

use App\Modules\Library\Enums\InventoryStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class InventoryRequest extends BaseRequest
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
            'copy_id' => ['required', 'integer', 'exists:library_copies,id'],
            'status' => ['required', Rule::in(InventoryStatus::values())],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
