<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class PromotionRequest extends BaseRequest
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
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'roll_number' => ['nullable', 'string', 'max:50'],
        ];
    }
}
