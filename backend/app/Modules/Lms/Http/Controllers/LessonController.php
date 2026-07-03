<?php

declare(strict_types=1);

namespace App\Modules\Lms\Http\Controllers;

use App\Modules\Lms\Enums\LmsStatus;
use App\Modules\Lms\Services\LessonService;
use App\Modules\Lms\Services\LmsAuthorizationService;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LessonController extends LmsContentController
{
    public function __construct(
        private readonly LessonService $service,
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
            'lesson_plan_id' => [$required, 'integer', 'exists:lms_lesson_plans,id'],
            'title' => [$required, 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'external_links' => ['nullable', 'array'],
            'embedded_videos' => ['nullable', 'array'],
            'estimated_duration' => ['nullable', 'integer', 'min:0'],
            'reading_time' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(LmsStatus::values())],
            'scheduled_at' => ['nullable', 'date'],
        ];
    }
}
