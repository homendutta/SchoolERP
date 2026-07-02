<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Enums\VerificationStatus;
use App\Modules\Inventory\Http\Requests\VerificationRequest;
use App\Modules\Inventory\Http\Resources\SimpleResource;
use App\Modules\Inventory\Models\Asset;
use App\Modules\Inventory\Services\VerificationService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends BaseController
{
    public function __construct(private readonly VerificationService $service) {}

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

    public function store(VerificationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $asset = Asset::query()->findOrFail($data['asset_id']);
        $record = $this->service->record($asset, VerificationStatus::from((string) $data['status']), $data['notes'] ?? null);

        return $this->ok(new SimpleResource($record), 'Verification recorded.', 201);
    }

    public function report(Request $request): JsonResponse
    {
        return $this->ok($this->service->report((int) $request->integer('school_id')));
    }
}
