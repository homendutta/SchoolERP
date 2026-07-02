<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Controllers;

use App\Modules\Library\Services\LibraryDashboardService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryDashboardController extends BaseController
{
    public function __construct(private readonly LibraryDashboardService $service) {}

    public function overview(Request $request): JsonResponse
    {
        $validated = $request->validate(['school_id' => ['nullable', 'integer']]);

        return $this->ok($this->service->overview(isset($validated['school_id']) ? (int) $validated['school_id'] : null));
    }
}
