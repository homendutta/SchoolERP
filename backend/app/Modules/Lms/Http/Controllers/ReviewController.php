<?php

declare(strict_types=1);

namespace App\Modules\Lms\Http\Controllers;

use App\Modules\Lms\Enums\ReviewAction;
use App\Modules\Lms\Http\Resources\SimpleResource;
use App\Modules\Lms\Models\Submission;
use App\Modules\Lms\Services\ReviewService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Teacher reviews of student submissions (append-only). */
class ReviewController extends BaseController
{
    public function __construct(private readonly ReviewService $service) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'submission_id' => ['required', 'integer', 'exists:lms_submissions,id'],
            'subject_id' => ['nullable', 'integer'],
            'action' => ['required', Rule::in(ReviewAction::values())],
            'comment' => ['nullable', 'string'],
            'marks' => ['nullable', 'numeric', 'min:0'],
        ]);

        $submission = Submission::query()->findOrFail($validated['submission_id']);
        $review = $this->service->review($request->user(), $submission, $validated);

        return $this->ok(new SimpleResource($review), 'Review recorded.', 201);
    }
}
