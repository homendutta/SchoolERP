<?php

declare(strict_types=1);

namespace App\Modules\Transport\Http\Controllers;

use App\Modules\Transport\Services\TransportDashboardService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransportDashboardController extends BaseController
{
    public function __construct(private readonly TransportDashboardService $service) {}

    public function overview(Request $request): JsonResponse
    {
        $validated = $request->validate(['school_id' => ['nullable', 'integer']]);

        return $this->ok($this->service->overview(isset($validated['school_id']) ? (int) $validated['school_id'] : null));
    }
}
