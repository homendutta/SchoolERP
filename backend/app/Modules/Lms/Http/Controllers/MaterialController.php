<?php

declare(strict_types=1);

namespace App\Modules\Lms\Http\Controllers;

use App\Modules\Lms\Enums\LmsStatus;
use App\Modules\Lms\Enums\MaterialType;
use App\Modules\Lms\Services\LmsAuthorizationService;
use App\Modules\Lms\Services\MaterialService;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaterialController extends LmsContentController
{
    public function __construct(
        private readonly MaterialService $service,
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
            'type' => ['sometimes', Rule::in(MaterialType::values())],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
            'topic' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(LmsStatus::values())],
        ];
    }
}
