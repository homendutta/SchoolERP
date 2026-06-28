<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class ExamGradeRequest extends BaseRequest
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
            'code' => [$required, 'string', 'max:10'],
            'name' => ['nullable', 'string', 'max:100'],
            'min_percentage' => [$required, 'numeric', 'min:0', 'max:100'],
            'max_percentage' => [$required, 'numeric', 'min:0', 'max:100'],
            'grade_point' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'is_failing' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', 'in:active,archived'],
        ];
    }
}
