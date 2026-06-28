<?php

declare(strict_types=1);

namespace App\Modules\Staff\Http\Controllers;

use App\Modules\Staff\Http\Requests\StaffRequest;
use App\Modules\Staff\Http\Resources\StaffResource;
use App\Modules\Staff\Models\Staff;
use App\Modules\Staff\Services\StaffService;
use App\Platform\Shared\Http\Controllers\BaseCrudController;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;

/**
 * Staff are created ONLY here (Staff Management owns creation). Enterprise
 * search, profile, create (with employee number) and update (with timeline).
 */
class StaffController extends BaseCrudController
{
    public function __construct(private readonly StaffService $service) {}

    protected function service(): BaseCrudService
    {
        return $this->service;
    }

    protected function resourceClass(): string
    {
        return StaffResource::class;
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->ok(new StaffResource($this->service->profile($id)));
    }

    public function store(StaffRequest $request): JsonResponse
    {
        return $this->created($request->validated());
    }

    public function update(StaffRequest $request, int|string $id): JsonResponse
    {
        /** @var Staff $staff */
        $staff = $this->service->find($id);

        return $this->ok(
            new StaffResource($this->service->updateProfile($staff, $request->validated())->load(['department', 'designation'])),
            'Staff updated.',
        );
    }
}
