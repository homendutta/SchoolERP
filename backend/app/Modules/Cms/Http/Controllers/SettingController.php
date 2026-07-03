<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Controllers;

use App\Modules\Cms\Http\Requests\SettingRequest;
use App\Modules\Cms\Http\Resources\SimpleResource;
use App\Modules\Cms\Services\SettingsService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Website settings — a per-school singleton (show + update). */
class SettingController extends BaseController
{
    public function __construct(private readonly SettingsService $service) {}

    public function show(Request $request): JsonResponse
    {
        $schoolId = $request->integer('school_id');

        return $this->ok(new SimpleResource($this->service->forSchool($schoolId)));
    }

    public function update(SettingRequest $request): JsonResponse
    {
        $data = $request->validated();

        return $this->ok(new SimpleResource($this->service->update((int) $data['school_id'], $data)), 'Settings saved.');
    }
}
