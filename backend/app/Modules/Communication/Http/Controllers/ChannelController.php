<?php

declare(strict_types=1);

namespace App\Modules\Communication\Http\Controllers;

use App\Modules\Communication\Enums\CommunicationChannel;
use App\Modules\Communication\Http\Requests\ChannelSettingRequest;
use App\Modules\Communication\Http\Resources\ChannelSettingResource;
use App\Modules\Communication\Services\ChannelSettingService;
use App\Modules\Communication\Support\Channels\ProviderRegistry;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Configurable channels: per-school settings + the active provider registry. */
class ChannelController extends BaseController
{
    public function __construct(
        private readonly ChannelSettingService $service,
        private readonly ProviderRegistry $registry,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->service->list($request->all());

        return $this->ok([
            'settings' => ChannelSettingResource::collection($page),
            'available_channels' => CommunicationChannel::values(),
            'active_providers' => $this->registry->channels(),
        ], null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    /** Upsert a channel's settings (enable/provider/retry policy). */
    public function store(ChannelSettingRequest $request): JsonResponse
    {
        $data = $request->validated();
        $setting = $this->service->upsert((int) $data['school_id'], (string) $data['channel'], $data);

        return $this->ok(new ChannelSettingResource($setting), 'Channel settings saved.');
    }
}
