<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Requests;

use App\Modules\Students\Enums\TransferType;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class TransferRequest extends BaseRequest
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
            'type' => ['required', Rule::enum(TransferType::class)],
            'to_class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'to_section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'transfer_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'destination_school' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
