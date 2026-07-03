<?php

declare(strict_types=1);

namespace App\Modules\Lms\Http\Controllers;

use App\Modules\Lms\Http\Resources\SimpleResource;
use App\Modules\Lms\Services\LmsAuthorizationService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Base for teacher-managed LMS content controllers. Enforces the teacher-subject
 * authorization boundary on every write (teachers manage only their assigned
 * subjects), then delegates to the publishing-aware content service.
 */
abstract class LmsContentController extends BaseCrudController
{
    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    abstract protected function auth(): LmsAuthorizationService;

    /**
     * Validation rules for this content type.
     *
     * @return array<string, mixed>
     */
    abstract protected function rules(Request $request): array;

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules($request));
        $this->authorizeSubject($request, $validated);
        $validated['teacher_id'] = $validated['teacher_id'] ?? $request->user()->id;

        return $this->created($validated);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $validated = $request->validate($this->rules($request));
        $this->authorizeSubject($request, $validated);

        return $this->updated($id, $validated);
    }

    /** @param array<string, mixed> $data */
    protected function authorizeSubject(Request $request, array $data): void
    {
        if (isset($data['subject_id'])) {
            $this->auth()->authorizeTeacherSubject(
                $request->user(),
                (int) $data['subject_id'],
                isset($data['class_id']) ? (int) $data['class_id'] : null,
                isset($data['section_id']) ? (int) $data['section_id'] : null,
            );
        } else {
            $this->auth()->requireTeacher($request->user());
        }
    }
}
