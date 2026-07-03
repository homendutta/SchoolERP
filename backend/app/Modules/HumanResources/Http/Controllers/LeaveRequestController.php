<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Controllers;

use App\Modules\HumanResources\Http\Requests\LeaveApplyRequest;
use App\Modules\HumanResources\Http\Requests\LeaveDecisionRequest;
use App\Modules\HumanResources\Http\Resources\SimpleResource;
use App\Modules\HumanResources\Models\LeaveRequest;
use App\Modules\HumanResources\Services\LeaveEngine;
use App\Modules\HumanResources\Services\LeaveRequestService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Leave requests — reads here; all writes go through the Leave Engine. */
class LeaveRequestController extends BaseController
{
    public function __construct(
        private readonly LeaveRequestService $service,
        private readonly LeaveEngine $engine,
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

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new SimpleResource($this->service->find($id)));
    }

    public function store(LeaveApplyRequest $request): JsonResponse
    {
        return $this->ok(new SimpleResource($this->engine->apply($request->validated())), 'Leave applied.', 201);
    }

    public function approve(LeaveDecisionRequest $request, int|string $id): JsonResponse
    {
        $leave = LeaveRequest::query()->findOrFail($id);

        return $this->ok(new SimpleResource($this->engine->approve($leave, $request->validated()['notes'] ?? null)), 'Leave approved.');
    }

    public function reject(LeaveDecisionRequest $request, int|string $id): JsonResponse
    {
        $leave = LeaveRequest::query()->findOrFail($id);

        return $this->ok(new SimpleResource($this->engine->reject($leave, $request->validated()['notes'] ?? null)), 'Leave rejected.');
    }

    public function cancel(int|string $id): JsonResponse
    {
        $leave = LeaveRequest::query()->findOrFail($id);

        return $this->ok(new SimpleResource($this->engine->cancel($leave)), 'Leave cancelled.');
    }
}
