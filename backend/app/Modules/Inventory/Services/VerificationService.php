<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Enums\AssetStatus;
use App\Modules\Inventory\Enums\VerificationStatus;
use App\Modules\Inventory\Models\Asset;
use App\Modules\Inventory\Models\Verification;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Physical verification. Every verification creates a historical record (never
 * overwritten). A missing/damaged result reflects onto the asset status without
 * deleting anything.
 */
class VerificationService extends BaseCrudService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly AssetLifecycleService $lifecycle,
    ) {}

    protected function model(): string
    {
        return Verification::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['asset:id,asset_number']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'asset_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'verified_at', 'created_at'];
    }

    public function record(Asset $asset, VerificationStatus $status, ?string $notes): Verification
    {
        return $this->transaction(function () use ($asset, $status, $notes): Verification {
            $verification = Verification::query()->create([
                'school_id' => $asset->school_id,
                'asset_id' => $asset->id,
                'status' => $status->value,
                'notes' => $notes,
                'verified_by' => Auth::id(),
                'verified_at' => Carbon::now(),
            ]);

            if ($status === VerificationStatus::Missing) {
                $this->lifecycle->transition($asset, AssetStatus::Lost, 'Marked missing at verification');
            } elseif ($status === VerificationStatus::Damaged) {
                $asset->update(['condition' => 'damaged']);
            }

            $this->activity->record('inventory.verified', "Asset {$asset->asset_number}: {$status->value}", $verification, [], (int) $asset->school_id, 'inventory');

            return $verification;
        });
    }

    /**
     * @return array<string, int>
     */
    public function report(int $schoolId): array
    {
        $latest = Verification::query()->where('school_id', $schoolId)
            ->get(['asset_id', 'status', 'id'])
            ->groupBy('asset_id')
            ->map(fn ($g) => $g->sortByDesc('id')->first()->status->value);

        $counts = ['verified' => 0, 'missing' => 0, 'damaged' => 0, 'disposed' => 0];
        foreach ($latest as $status) {
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }

        return $counts;
    }
}
