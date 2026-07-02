<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Controllers;

use App\Modules\Hostel\Actions\TransferBedAction;
use App\Modules\Hostel\Http\Requests\TransferRequest;
use App\Modules\Hostel\Http\Resources\SimpleResource;
use App\Modules\Hostel\Services\TransferService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransferController extends BaseController
{
    public function __construct(private readonly TransferService $service) {}

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

    public function transfer(TransferRequest $request, TransferBedAction $action): JsonResponse
    {
        /** @var array{student_id:int, to_bed_id:int, reason?:string|null, transfer_type?:string|null} $data */
        $data = $request->validated();
        $allocation = $action->handle($data)->load(['student:id,name', 'room:id,room_number', 'bed:id,bed_number']);

        return $this->ok(new SimpleResource($allocation), 'Transferred.', 201);
    }
}
