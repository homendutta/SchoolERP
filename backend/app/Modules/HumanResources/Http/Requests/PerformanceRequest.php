<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Requests;

use App\Modules\HumanResources\Enums\ReviewStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class PerformanceRequest extends BaseRequest
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
            'reviewer_id' => ['nullable', 'integer', 'exists:staff,id'],
            'review_period_start' => ['nullable', 'date'],
            'review_period_end' => ['nullable', 'date'],
            'goals' => ['nullable', 'string'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'comments' => ['nullable', 'string'],
            'development_plan' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(ReviewStatus::values())],
        ];
    }
}
