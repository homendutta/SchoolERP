<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Enums\AssetStatus;
use App\Modules\Inventory\Enums\DisposalMethod;
use App\Modules\Inventory\Models\Asset;
use App\Modules\Inventory\Models\Disposal;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Asset disposal. Disposal history is preserved; a disposed asset's status is
 * set to Disposed but the asset is NEVER deleted.
 */
class DisposalService extends BaseCrudService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly AssetLifecycleService $lifecycle,
    ) {}

    protected function model(): string
    {
        return Disposal::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['asset:id,asset_number']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'asset_id', 'method'];
    }

    protected function sortable(): array
    {
        return ['id', 'disposal_date'];
    }

    /**
     * @param  array{reason?:string|null, disposal_date?:string|null, value?:float|null}  $meta
     */
    public function dispose(Asset $asset, DisposalMethod $method, array $meta = []): Disposal
    {
        return $this->transaction(function () use ($asset, $method, $meta): Disposal {
            $disposal = Disposal::query()->create([
                'school_id' => $asset->school_id,
                'asset_id' => $asset->id,
                'method' => $method->value,
                'reason' => $meta['reason'] ?? null,
                'disposal_date' => $meta['disposal_date'] ?? now()->toDateString(),
                'value' => $meta['value'] ?? null,
                'approved_by' => Auth::id(),
            ]);

            $this->lifecycle->transition($asset, AssetStatus::Disposed, "Disposed ({$method->value})");

            $this->activity->record('inventory.disposed', "Asset {$asset->asset_number} disposed ({$method->value})", $disposal, [], (int) $asset->school_id, 'inventory');

            return $disposal;
        });
    }
}
