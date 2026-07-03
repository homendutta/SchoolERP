<?php

declare(strict_types=1);

namespace App\Modules\Lms\Http\Controllers;

use App\Modules\Lms\Http\Resources\SimpleResource;
use App\Modules\Lms\Models\Discussion;
use App\Modules\Lms\Models\DiscussionPost;
use App\Modules\Lms\Services\DiscussionService;
use App\Modules\Lms\Services\LmsAuthorizationService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Classroom discussions (teacher-created + moderated; students reply). */
class DiscussionController extends BaseCrudController
{
    public function __construct(
        private readonly DiscussionService $service,
        private readonly LmsAuthorizationService $auth,
    ) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return SimpleResource::class;
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules($request));
        if (isset($validated['subject_id'])) {
            $this->auth->authorizeTeacherSubject($request->user(), (int) $validated['subject_id']);
        } else {
            $this->auth->requireTeacher($request->user());
        }
        $validated['teacher_id'] = $request->user()->id;

        return $this->created($validated);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $this->auth->requireTeacher($request->user());

        return $this->updated($id, $request->validate($this->rules($request)));
    }

    /** Post a reply (student or teacher). */
    public function post(Request $request, int|string $id): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string'],
            'student_id' => ['nullable', 'integer'],
        ]);
        $discussion = Discussion::query()->findOrFail($id);
        $post = $this->service->post($request->user(), $discussion, $validated['body'], isset($validated['student_id']) ? (int) $validated['student_id'] : null);

        return $this->ok(new SimpleResource($post), 'Posted.', 201);
    }

    /** Teacher moderation — hide a post. */
    public function moderate(Request $request, int|string $postId): JsonResponse
    {
        $post = DiscussionPost::query()->findOrFail($postId);

        return $this->ok(new SimpleResource($this->service->moderatePost($request->user(), $post)), 'Post hidden.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(Request $request): array
    {
        $required = $request->isMethod('post') ? 'required' : 'sometimes';

        return [
            'school_id' => [$required, 'integer', 'exists:schools,id'],
            'subject_id' => ['nullable', 'integer'],
            'class_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'title' => [$required, 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'locked' => ['nullable', 'boolean'],
        ];
    }
}
