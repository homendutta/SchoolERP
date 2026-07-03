<?php

declare(strict_types=1);

namespace App\Modules\Lms\Http\Controllers;

use App\Modules\Lms\Enums\LmsStatus;
use App\Modules\Lms\Services\LessonPlanService;
use App\Modules\Lms\Services\LmsAuthorizationService;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LessonPlanController extends LmsContentController
{
    public function __construct(
        private readonly LessonPlanService $service,
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
            'academic_year_id' => ['nullable', 'integer'],
            'class_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'teacher_id' => ['nullable', 'integer'],
            'title' => [$required, 'string', 'max:255'],
            'objectives' => ['nullable', 'string'],
            'topics' => ['nullable', 'string'],
            'teaching_method' => ['nullable', 'string', 'max:255'],
            'planned_date' => ['nullable', 'date'],
            'completion_status' => ['nullable', 'string', 'in:planned,in_progress,completed'],
            'notes' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(LmsStatus::values())],
        ];
    }
}
