<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Controllers;

use App\Modules\Timetable\Http\Requests\WorkingDaysRequest;
use App\Modules\Timetable\Http\Resources\WorkingDayResource;
use App\Modules\Timetable\Services\WorkingDayService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkingDayController extends BaseController
{
    public function __construct(private readonly WorkingDayService $service) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->service->list($request->all() + ['sort' => 'sort_order', 'per_page' => 7]);

        return $this->ok(WorkingDayResource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    /** Upsert the school's working-day configuration in one call. */
    public function sync(WorkingDaysRequest $request): JsonResponse
    {
        /** @var array{school_id:int, days:array<int, array{weekday:string, is_working?:bool}>} $data */
        $data = $request->validated();

        $days = $this->service->sync($data['school_id'], $data['days']);

        return $this->ok(WorkingDayResource::collection(collect($days)), 'Working days updated.');
    }
}
