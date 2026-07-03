<?php

declare(strict_types=1);

namespace App\Modules\Lms\Http\Controllers;

use App\Modules\Lms\Enums\LmsStatus;
use App\Modules\Lms\Enums\QuestionType;
use App\Modules\Lms\Services\LmsAuthorizationService;
use App\Modules\Lms\Services\QuizService;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuizController extends LmsContentController
{
    public function __construct(
        private readonly QuizService $service,
        private readonly LmsAuthorizationService $authorization,
    ) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function auth(): LmsAuthorizationService
    {
        return $this->authorization;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(Request $request): array
    {
        $required = $request->isMethod('post') ? 'required' : 'sometimes';

        return [
            'school_id' => [$required, 'integer', 'exists:schools,id'],
            'subject_id' => [$required, 'integer'],
            'class_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'teacher_id' => ['nullable', 'integer'],
            'title' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'time_limit' => ['nullable', 'integer', 'min:0'],
            'passing_marks' => ['nullable', 'numeric', 'min:0'],
            'random_order' => ['nullable', 'boolean'],
            'immediate_result' => ['nullable', 'boolean'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'status' => ['sometimes', Rule::in(LmsStatus::values())],
            'questions' => ['nullable', 'array'],
            'questions.*.type' => ['required_with:questions', Rule::in(QuestionType::values())],
            'questions.*.question' => ['required_with:questions', 'string'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.answer' => ['nullable', 'array'],
            'questions.*.marks' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
