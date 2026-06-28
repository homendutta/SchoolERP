<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Controllers;

use App\Modules\Timetable\Http\Resources\ClassTimetableResource;
use App\Modules\Timetable\Services\DerivedTimetableService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Room timetable — DERIVED from the master class timetable. */
class RoomTimetableController extends BaseController
{
    public function __construct(private readonly DerivedTimetableService $derived) {}

    public function show(int|string $roomId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'integer'],
            'template_id' => ['nullable', 'integer'],
        ]);

        $rows = $this->derived->forRoom(
            (int) $roomId,
            (int) $validated['academic_year_id'],
            isset($validated['template_id']) ? (int) $validated['template_id'] : null,
        );

        return $this->ok(ClassTimetableResource::collection($rows));
    }
}
