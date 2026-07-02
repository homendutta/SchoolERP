<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Controllers;

use App\Modules\Library\Http\Resources\BorrowingResource;
use App\Modules\Library\Services\BorrowingService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Read-only listing of borrowing transactions (returns/renewals are actions). */
class BorrowingController extends BaseController
{
    public function __construct(private readonly BorrowingService $service) {}

    public function index(Request $request): JsonResponse
    {
        if ($request->has('school_id')) {
            $this->service->markOverdue((int) $request->integer('school_id'));
        }

        $page = $this->service->list($request->all());

        return $this->ok(BorrowingResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new BorrowingResource($this->service->find($id)->load(['book:id,title', 'copy:id,copy_number', 'owner', 'renewals'])));
    }
}
