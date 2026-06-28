<?php

declare(strict_types=1);

namespace App\Modules\Communication\Http\Controllers;

use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Http\Requests\PreferenceRequest;
use App\Modules\Communication\Models\CommunicationPreference;
use App\Modules\Communication\Services\PreferenceService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Per-user channel preferences (respected unless a message is mandatory). */
class PreferenceController extends BaseController
{
    public function __construct(private readonly PreferenceService $service) {}

    public function index(Request $request): JsonResponse
    {
        $userId = $request->has('user_id') ? (int) $request->integer('user_id') : Auth::id();

        $existing = CommunicationPreference::query()->where('user_id', $userId)->get()->keyBy(fn ($p) => $p->channel->value);

        $prefs = array_map(fn (CommunicationChannel $c) => [
            'channel' => $c->value,
            'is_enabled' => $existing->get($c->value)?->is_enabled ?? true,
        ], CommunicationChannel::active());

        return $this->ok(['user_id' => $userId, 'preferences' => $prefs]);
    }

    public function update(PreferenceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $userId = isset($data['user_id']) ? (int) $data['user_id'] : (int) Auth::id();

        foreach ($data['preferences'] as $pref) {
            $this->service->set($userId, CommunicationChannel::from((string) $pref['channel']), (bool) $pref['is_enabled']);
        }

        return $this->ok(null, 'Preferences updated.');
    }
}
