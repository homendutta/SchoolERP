<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Enums\AssetStatus;
use App\Modules\Inventory\Models\Asset;
use App\Modules\Inventory\Models\LifecycleEvent;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Facades\Auth;

/**
 * The Asset Lifecycle engine. Every lifecycle transition updates the asset's
 * current state, writes an immutable Timeline entry (asset_lifecycle_events) and
 * an Audit Log entry. Assets are never deleted; disposed assets remain
 * searchable with their full history.
 */
class AssetLifecycleService extends BaseService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function transition(Asset $asset, AssetStatus $to, ?string $note = null, ?int $by = null): LifecycleEvent
    {
        return $this->transaction(function () use ($asset, $to, $note, $by): LifecycleEvent {
            $from = $asset->status instanceof AssetStatus ? $asset->status : null;

            $event = LifecycleEvent::query()->create([
                'school_id' => $asset->school_id,
                'asset_id' => $asset->id,
                'from_status' => $from?->value,
                'to_status' => $to->value,
                'note' => $note,
                'changed_by' => $by ?? Auth::id(),
                'created_at' => now(),
            ]);

            $asset->update(['status' => $to->value]);

            $this->activity->record(
                'inventory.lifecycle_transition',
                "Asset {$asset->asset_number}: ".($from?->value ?? 'new')." → {$to->value}",
                $asset,
                ['from' => $from?->value, 'to' => $to->value, 'note' => $note],
                (int) $asset->school_id,
                'inventory',
            );

            return $event;
        });
    }

    /** Record the initial lifecycle state when an asset is created. */
    public function recordInitial(Asset $asset): LifecycleEvent
    {
        return LifecycleEvent::query()->create([
            'school_id' => $asset->school_id,
            'asset_id' => $asset->id,
            'from_status' => null,
            'to_status' => ($asset->status instanceof AssetStatus ? $asset->status : AssetStatus::Available)->value,
            'note' => 'Asset registered',
            'changed_by' => Auth::id(),
            'created_at' => now(),
        ]);
    }
}
