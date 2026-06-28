<?php

declare(strict_types=1);

namespace App\Modules\Students\Http\Controllers;

use App\Modules\Students\Http\Resources\TimelineResource;
use App\Modules\Students\Services\StudentTimelineService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimelineController extends BaseController
{
    public function __construct(private readonly StudentTimelineService $service) {}

    /** A student's timeline, newest first. */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['student_id' => ['required', 'integer']]);

        return $this->ok(TimelineResource::collection($this->service->forStudent($request->integer('student_id'))));
    }

    /** Record a manual timeline note. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'event_type' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $entry = $this->service->record(
            $data['student_id'],
            $data['event_type'],
            $data['title'],
            $data['description'] ?? null,
        );

        return $this->ok(new TimelineResource($entry), 'Timeline entry added.', 201);
    }
}
