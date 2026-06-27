<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Controllers;

use App\Modules\Administration\Enums\SettingGroup;
use App\Modules\Administration\Services\SettingsService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Settings Engine — dynamic, grouped settings.
 *   GET  /api/v1/admin/settings           all groups
 *   GET  /api/v1/admin/settings/{group}   one group
 *   PUT  /api/v1/admin/settings/{group}   upsert keys for a group
 */
class SettingsController extends BaseController
{
    public function __construct(private readonly SettingsService $service) {}

    public function index(): JsonResponse
    {
        return $this->ok($this->service->all());
    }

    public function show(string $group): JsonResponse
    {
        $this->assertGroup($group);

        return $this->ok($this->service->group($group));
    }

    public function update(Request $request, string $group): JsonResponse
    {
        $this->assertGroup($group);
        $values = (array) $request->input('values', $request->except(['_method']));

        return $this->ok($this->service->updateGroup($group, $values), 'Settings updated.');
    }

    private function assertGroup(string $group): void
    {
        if (! in_array($group, SettingGroup::values(), true)) {
            throw ValidationException::withMessages(['group' => "Unknown settings group '{$group}'."]);
        }
    }
}
