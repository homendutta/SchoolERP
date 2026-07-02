<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Enums\AssetStatus;
use App\Modules\Inventory\Enums\AssignmentStatus;
use App\Modules\Inventory\Models\Asset;
use App\Modules\Inventory\Models\AssetAssignment;
use App\Modules\Inventory\Models\Consumable;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\Verification;
use App\Modules\Inventory\Models\Warranty;
use App\Platform\Foundation\Maintenance\Enums\MaintenanceStatus;
use App\Platform\Foundation\Maintenance\Models\MaintenanceRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class InventoryDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(?int $schoolId): array
    {
        $assets = fn (): Builder => Asset::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));
        $consumables = fn (): Builder => Consumable::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));

        $lowStock = (clone $consumables())->whereColumn('current_stock', '<=', 'minimum_stock')->count();
        $warrantyExpiring = Warranty::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereBetween('end_date', [Carbon::now()->toDateString(), Carbon::now()->addDays(30)->toDateString()])->count();
        $maintenanceDue = MaintenanceRequest::query()->where('maintainable_type', Asset::class)
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereIn('status', [MaintenanceStatus::Scheduled->value, MaintenanceStatus::InProgress->value])->count();

        return [
            'widgets' => [
                'total_assets' => (clone $assets())->count(),
                'active_assets' => (clone $assets())->where('status', AssetStatus::Available->value)->count(),
                'assigned_assets' => (clone $assets())->where('status', AssetStatus::Assigned->value)->count(),
                'consumables' => (clone $consumables())->count(),
                'low_stock' => $lowStock,
                'warranty_expiring' => $warrantyExpiring,
                'maintenance_due' => $maintenanceDue,
                'verification_pending' => (clone $assets())->whereNotIn('id', Verification::query()->select('asset_id'))->count(),
            ],
            'charts' => [
                'category_distribution' => (clone $assets())->get(['category_id'])
                    ->groupBy('category_id')
                    ->map(fn ($g, $cid) => ['label' => "Category #{$cid}", 'count' => $g->count()])->values()->all(),
                'asset_allocation' => AssetAssignment::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
                    ->where('status', AssignmentStatus::Active->value)->get(['target_type'])
                    ->groupBy(fn ($a) => $a->target_type->value)
                    ->map(fn ($g, $t) => ['label' => $t, 'count' => $g->count()])->values()->all(),
                'maintenance_trend' => $this->trend(MaintenanceRequest::query()->where('maintainable_type', Asset::class)->when($schoolId, fn ($q) => $q->where('school_id', $schoolId)), 'created_at'),
                'stock_consumption' => StockMovement::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
                    ->where('type', 'out')->get(['created_at'])
                    ->groupBy(fn ($m) => Carbon::parse($m->created_at)->format('Y-m-d'))
                    ->map(fn ($g, $p) => ['label' => $p, 'count' => $g->count()])->sortKeys()->values()->take(-14)->all(),
            ],
        ];
    }

    /**
     * @return array<int, array{label:string, count:int}>
     */
    private function trend(Builder $query, string $column): array
    {
        return $query->get([$column])
            ->groupBy(fn ($m) => Carbon::parse($m->getAttribute($column))->format('Y-m-d'))
            ->map(fn ($g, $period) => ['label' => $period, 'count' => $g->count()])
            ->sortKeys()->values()->take(-14)->all();
    }
}
