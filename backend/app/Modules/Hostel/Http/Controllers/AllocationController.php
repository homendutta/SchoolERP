<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Controllers;

use App\Modules\Hostel\Actions\AllocateBedAction;
use App\Modules\Hostel\Http\Requests\AllocateRequest;
use App\Modules\Hostel\Http\Resources\SimpleResource;
use App\Modules\Hostel\Models\Allocation;
use App\Modules\Hostel\Services\AllocationEngine;
use App\Modules\Hostel\Services\AllocationService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Student bed allocations. Students occupy beds (never rooms directly); a bed
 * can never have two active occupants; history is never overwritten.
 */
class AllocationController extends BaseController
{
    public function __construct(
        private readonly AllocationService $service,
        private readonly AllocationEngine $engine,
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

    public function allocate(AllocateRequest $request, AllocateBedAction $action): JsonResponse
    {
        /** @var array{student_id:int, bed_id:int, academic_year_id?:int|null} $data */
        $data = $request->validated();
        $allocation = $action->handle($data)->load(['student:id,name', 'hostel:id,name', 'room:id,room_number', 'bed:id,bed_number']);

        return $this->ok(new SimpleResource($allocation), 'Allocated.', 201);
    }

    public function checkout(int|string $id): JsonResponse
    {
        $allocation = $this->engine->checkout(Allocation::query()->findOrFail($id));

        return $this->ok(new SimpleResource($allocation), 'Checked out.');
    }
}
