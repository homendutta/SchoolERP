<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Requests;

use App\Modules\Library\Enums\CopyCondition;
use App\Modules\Library\Enums\CopyStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class CopyRequest extends BaseRequest
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
            'book_id' => [$required, 'integer', 'exists:library_books,id'],
            'copy_number' => [$required, 'string', 'max:100'],
            'location_id' => ['nullable', 'integer', 'exists:library_locations,id'],
            'shelf' => ['nullable', 'string', 'max:50'],
            'rack' => ['nullable', 'string', 'max:50'],
            'acquisition_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'condition' => ['sometimes', Rule::in(CopyCondition::values())],
            'status' => ['sometimes', Rule::in(CopyStatus::values())],
        ];
    }
}
