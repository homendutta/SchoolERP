<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Controllers;

use App\Modules\Library\Http\Resources\ReservationResource;
use App\Modules\Library\Models\Reservation;
use App\Modules\Library\Services\ReservationService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends BaseController
{
    public function __construct(private readonly ReservationService $service) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->service->list($request->all() + ['sort' => 'queue_position']);

        return $this->ok(ReservationResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    public function cancel(int|string $id): JsonResponse
    {
        $reservation = $this->service->cancel(Reservation::query()->findOrFail($id));

        return $this->ok(new ReservationResource($reservation), 'Reservation cancelled.');
    }
}
