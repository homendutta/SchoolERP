<?php

declare(strict_types=1);

namespace App\Modules\System\Http\Controllers;

use App\Modules\System\Services\FailedJobsService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

/** Failed-job monitoring + dead-letter handling. */
class FailedJobsController extends BaseController
{
    public function __construct(private readonly FailedJobsService $service) {}

    public function index(): JsonResponse
    {
        return $this->ok([
            'available' => $this->service->available(),
            'count' => $this->service->count(),
            'jobs' => $this->service->list(),
        ]);
    }

    public function retry(string $id): JsonResponse
    {
        return $this->ok(['retried' => $this->service->retry($id)], 'Retry requested.');
    }

    public function forget(string $id): JsonResponse
    {
        return $this->ok(['forgotten' => $this->service->forget($id)], 'Removed.');
    }
}
