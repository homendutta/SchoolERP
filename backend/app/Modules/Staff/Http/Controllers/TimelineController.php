<?php

declare(strict_types=1);

namespace App\Modules\Staff\Http\Controllers;

use App\Modules\Staff\Http\Resources\TimelineResource;
use App\Modules\Staff\Services\StaffTimelineService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimelineController extends BaseController
{
    public function __construct(private readonly StaffTimelineService $service) {}

    /** A staff member's timeline, newest first. */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['staff_id' => ['required', 'integer']]);

        return $this->ok(TimelineResource::collection($this->service->forStaff($request->integer('staff_id'))));
    }

    /** Record a manual timeline note. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'staff_id' => ['required', 'integer', 'exists:staff,id'],
            'event_type' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $entry = $this->service->record($data['staff_id'], $data['event_type'], $data['title'], $data['description'] ?? null);

        return $this->ok(new TimelineResource($entry), 'Timeline entry added.', 201);
    }
}
