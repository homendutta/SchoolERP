<?php

declare(strict_types=1);

namespace App\Modules\Lms\Http\Controllers;

use App\Modules\Lms\Http\Resources\SimpleResource;
use App\Modules\Lms\Services\LmsAuthorizationService;
use App\Modules\Lms\Services\SubmissionService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Student submissions — immutable versions; parents/students isolated. */
class SubmissionController extends BaseController
{
    public function __construct(
        private readonly SubmissionService $service,
        private readonly LmsAuthorizationService $auth,
    ) {}

    /** Immutable submission history for a homework/assignment + authorized student. */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:homework,assignment'],
            'submittable_id' => ['required', 'integer'],
            'student_id' => ['required', 'integer'],
        ]);
        $this->auth->authorizeStudent($request->user(), (int) $validated['student_id']);

        return $this->ok($this->service->history($validated['type'], (int) $validated['submittable_id'], (int) $validated['student_id']));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:homework,assignment'],
            'submittable_id' => ['required', 'integer'],
            'student_id' => ['required', 'integer'],
            'content' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['integer', 'exists:media,id'],
            'links' => ['nullable', 'array'],
        ]);

        $submission = $this->service->submit(
            $request->user(),
            $validated['type'],
            (int) $validated['submittable_id'],
            (int) $validated['student_id'],
            $validated,
        );

        return $this->ok(new SimpleResource($submission), 'Submission received.', 201);
    }
}
