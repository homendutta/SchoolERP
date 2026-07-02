<?php

declare(strict_types=1);

namespace App\Modules\Transport\Http\Controllers;

use App\Modules\Transport\Actions\AssignStudentAction;
use App\Modules\Transport\Http\Requests\AssignStudentRequest;
use App\Modules\Transport\Http\Resources\SimpleResource;
use App\Modules\Transport\Models\StudentAssignment;
use App\Modules\Transport\Services\StudentAssignmentEngine;
use App\Modules\Transport\Services\StudentAssignmentService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Student transport assignments. Students are assigned to a route + stop (never
 * directly to a vehicle); the vehicle is determined via the trip.
 */
class StudentAssignmentController extends BaseController
{
    public function __construct(
        private readonly StudentAssignmentService $service,
        private readonly StudentAssignmentEngine $engine,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->service->list($request->all());

        return $this->ok(SimpleResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    public function assign(AssignStudentRequest $request, AssignStudentAction $action): JsonResponse
    {
        /** @var array{student_id:int, route_id:int, stop_id:int, academic_year_id?:int|null, shift?:string|null} $data */
        $data = $request->validated();
        $assignment = $action->handle($data)->load(['student:id,name', 'route:id,name', 'stop:id,name']);

        return $this->ok(new SimpleResource($assignment), 'Student assigned.', 201);
    }

    public function cancel(int|string $id): JsonResponse
    {
        $assignment = $this->engine->cancel(StudentAssignment::query()->findOrFail($id));

        return $this->ok(new SimpleResource($assignment), 'Assignment cancelled.');
    }
}
