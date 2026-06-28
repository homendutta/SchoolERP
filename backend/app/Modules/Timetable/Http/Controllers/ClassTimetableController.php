<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Controllers;

use App\Modules\Timetable\Actions\SaveTimetableEntryAction;
use App\Modules\Timetable\Http\Requests\ClassTimetableRequest;
use App\Modules\Timetable\Http\Resources\ClassTimetableResource;
use App\Modules\Timetable\Services\ClassTimetableService;
use App\Modules\Timetable\Services\DerivedTimetableService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The master class timetable. Reads use the search/filter service; writes go
 * through the SaveTimetableEntryAction so clash detection always runs.
 */
class ClassTimetableController extends BaseController
{
    public function __construct(private readonly ClassTimetableService $service) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->service->list($request->all());

        return $this->ok(ClassTimetableResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    /** Relations shown on a single slot. */
    private const RELATIONS = [
        'period:id,name,code,start_time,end_time,sort_order',
        'subject:id,name,code',
        'teacher:id,name,employee_number',
        'room:id,name,code',
        'schoolClass:id,name',
        'section:id,name',
        'template:id,name',
    ];

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new ClassTimetableResource($this->service->find($id)->load(self::RELATIONS)));
    }

    public function store(ClassTimetableRequest $request, SaveTimetableEntryAction $action): JsonResponse
    {
        $entry = $action->handle($request->toData());

        return $this->ok(new ClassTimetableResource($entry->load(self::RELATIONS)), 'Slot saved.', 201);
    }

    public function update(ClassTimetableRequest $request, int|string $id, SaveTimetableEntryAction $action): JsonResponse
    {
        $entry = $action->handle($request->toData(), (int) $id);

        return $this->ok(new ClassTimetableResource($entry->load(self::RELATIONS)), 'Slot updated.');
    }

    public function destroy(int|string $id): JsonResponse
    {
        $this->service->delete($this->service->find($id));

        return $this->ok(null, 'Slot deleted.');
    }

    /** Derived grid for a class+section (Phase 3 view). */
    public function grid(Request $request, DerivedTimetableService $derived): JsonResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'academic_year_id' => ['required', 'integer'],
            'template_id' => ['nullable', 'integer'],
        ]);

        $rows = $derived->forClass(
            (int) $validated['class_id'],
            isset($validated['section_id']) ? (int) $validated['section_id'] : null,
            (int) $validated['academic_year_id'],
            isset($validated['template_id']) ? (int) $validated['template_id'] : null,
        );

        return $this->ok(ClassTimetableResource::collection($rows));
    }
}
