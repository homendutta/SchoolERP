<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Services\InventoryDashboardService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryDashboardController extends BaseController
{
    public function __construct(private readonly InventoryDashboardService $service) {}

    public function overview(Request $request): JsonResponse
    {
        $validated = $request->validate(['school_id' => ['nullable', 'integer']]);

        return $this->ok($this->service->overview(isset($validated['school_id']) ? (int) $validated['school_id'] : null));
    }
}
