<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Controllers;

use App\Modules\Administration\Http\Requests\UpdateSchoolRequest;
use App\Modules\Administration\Http\Resources\SchoolResource;
use App\Modules\Administration\Services\SchoolSettingsService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

/**
 * School Settings — GET/PUT /api/v1/admin/school. Thin: delegates to the service.
 */
class SchoolSettingsController extends BaseController
{
    public function __construct(private readonly SchoolSettingsService $service) {}

    public function show(): JsonResponse
    {
        return $this->ok(new SchoolResource($this->service->current()));
    }

    public function update(UpdateSchoolRequest $request): JsonResponse
    {
        $school = $this->service->update($request->validated());

        return $this->ok(new SchoolResource($school), 'School settings updated.');
    }
}
