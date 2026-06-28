<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Requests;

use App\Modules\Examination\Enums\ExamSessionStatus;
use App\Modules\Examination\Enums\RankingMethod;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ExamSessionRequest extends BaseRequest
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
            'academic_year_id' => [$required, 'integer', 'exists:academic_years,id'],
            'term_id' => ['nullable', 'integer', 'exists:terms,id'],
            'exam_type_id' => [$required, 'integer', 'exists:exam_types,id'],
            'name' => [$required, 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['sometimes', Rule::in(ExamSessionStatus::values())],
            'ranking_method' => ['sometimes', Rule::in(RankingMethod::values())],
            'description' => ['nullable', 'string'],
        ];
    }
}
