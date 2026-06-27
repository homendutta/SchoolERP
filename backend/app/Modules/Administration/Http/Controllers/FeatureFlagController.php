<?php

declare(strict_types=1);

namespace App\Modules\Administration\Http\Controllers;

use App\Modules\Administration\Enums\FeatureFlagKey;
use App\Modules\Administration\Models\FeatureFlag;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Feature Flags — enable/disable optional product modules.
 *   GET /api/v1/admin/feature-flags        PUT /api/v1/admin/feature-flags/{key}
 */
class FeatureFlagController extends BaseController
{
    public function index(): JsonResponse
    {
        // Ensure all known flags exist, then return them.
        foreach (FeatureFlagKey::cases() as $flag) {
            FeatureFlag::query()->firstOrCreate(['key' => $flag->value], ['label' => $flag->label()]);
        }

        $flags = FeatureFlag::query()->orderBy('key')->get()
            ->map(fn (FeatureFlag $f) => [
                'key' => $f->key,
                'label' => $f->label,
                'is_enabled' => $f->is_enabled,
            ]);

        return $this->ok($flags);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $flag = FeatureFlag::query()->where('key', $key)->firstOrFail();
        $flag->update(['is_enabled' => (bool) $request->boolean('is_enabled')]);

        return $this->ok([
            'key' => $flag->key,
            'label' => $flag->label,
            'is_enabled' => $flag->is_enabled,
        ], 'Feature flag updated.');
    }
}
