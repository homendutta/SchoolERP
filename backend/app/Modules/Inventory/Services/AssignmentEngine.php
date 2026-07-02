<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Enums\AssetStatus;
use App\Modules\Inventory\Enums\AssignmentStatus;
use App\Modules\Inventory\Models\Asset;
use App\Modules\Inventory\Models\AssetAssignment;
use App\Modules\Inventory\Models\AssetTransfer;
use App\Modules\Staff\Models\Staff;
use App\Modules\Staff\Services\StaffTimelineService;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Services\StudentTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Foundation\Identity\IdentityService;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Carbon;

/**
 * Asset assignment engine. Assignments resolve through the Platform Identity
 * Service (never coupled to Staff/Room/Hostel primary keys): person targets pass
 * an identity_number that resolves to an Identity + owner; non-person targets
 * (department / room / hostel / library / laboratory) use a decoupled
 * target_reference string. Assignments and transfers are HISTORICAL — a return
 * or transfer closes the current record and creates a new one. Asset lifecycle,
 * Audit, Timeline and Communication events are written on every change.
 */
class AssignmentEngine extends BaseService
{
    public function __construct(
        private readonly AssetLifecycleService $lifecycle,
        private readonly IdentityService $identities,
        private readonly ActivityLogger $activity,
        private readonly StaffTimelineService $staffTimeline,
        private readonly StudentTimelineService $studentTimeline,
        private readonly InventoryHooks $hooks,
    ) {}

    /**
     * @param  array{target_type:string, identity_number?:string|null, target_reference?:string|null, target_label?:string|null}  $data
     */
    public function assign(Asset $asset, array $data, ?int $by = null): AssetAssignment
    {
        return $this->transaction(function () use ($asset, $data, $by): AssetAssignment {
            $this->closeActive($asset, AssignmentStatus::Returned);

            $assignment = $this->createAssignment($asset, $data, $by);
            $this->lifecycle->transition($asset, AssetStatus::Assigned, 'Assigned to '.($assignment->target_label ?? $data['target_type']), $by);

            $this->timeline($assignment);
            $this->activity->record('inventory.asset_assigned', "Asset {$asset->asset_number} assigned", $assignment, [
                'target_type' => $data['target_type'], 'identity_id' => $assignment->identity_id,
            ], (int) $asset->school_id, 'inventory');
            $this->hooks->assetAssigned((int) $asset->school_id, "Asset {$asset->asset_number} assigned to ".($assignment->target_label ?? $data['target_type']).'.');

            return $assignment->refresh();
        });
    }

    /**
     * @param  array{target_type:string, identity_number?:string|null, target_reference?:string|null, target_label?:string|null, reason?:string|null, transfer_type?:string|null}  $data
     */
    public function transfer(Asset $asset, array $data, ?int $by = null): AssetAssignment
    {
        return $this->transaction(function () use ($asset, $data, $by): AssetAssignment {
            $current = AssetAssignment::query()
                ->where('asset_id', $asset->id)->where('status', AssignmentStatus::Active->value)
                ->latest('id')->first();

            if ($current === null) {
                throw BusinessRuleException::make('The asset has no active assignment to transfer.', 'NO_ACTIVE_ASSIGNMENT');
            }

            $current->update(['status' => AssignmentStatus::Transferred->value, 'returned_on' => Carbon::now()->toDateString()]);
            $new = $this->createAssignment($asset, $data, $by);

            AssetTransfer::query()->create([
                'school_id' => $asset->school_id,
                'asset_id' => $asset->id,
                'from_assignment_id' => $current->id,
                'to_assignment_id' => $new->id,
                'from_label' => $current->target_label,
                'to_label' => $new->target_label,
                'transfer_type' => $data['transfer_type'] ?? $data['target_type'],
                'reason' => $data['reason'] ?? null,
                'transfer_date' => Carbon::now()->toDateString(),
                'performed_by' => $by,
            ]);

            $this->timeline($new);
            $this->activity->record('inventory.asset_transferred', "Asset {$asset->asset_number} transferred", $new, [
                'from' => $current->target_label, 'to' => $new->target_label,
            ], (int) $asset->school_id, 'inventory');

            return $new->refresh();
        });
    }

    public function returnAsset(AssetAssignment $assignment): AssetAssignment
    {
        return $this->transaction(function () use ($assignment): AssetAssignment {
            $assignment->update(['status' => AssignmentStatus::Returned->value, 'returned_on' => Carbon::now()->toDateString()]);
            $asset = Asset::query()->findOrFail($assignment->asset_id);
            $this->lifecycle->transition($asset, AssetStatus::Available, 'Returned');

            $this->activity->record('inventory.asset_returned', "Asset {$asset->asset_number} returned", $assignment, [], (int) $asset->school_id, 'inventory');
            $this->hooks->assetReturned((int) $asset->school_id, "Asset {$asset->asset_number} returned.");

            return $assignment->refresh();
        });
    }

    private function closeActive(Asset $asset, AssignmentStatus $status): void
    {
        AssetAssignment::query()
            ->where('asset_id', $asset->id)
            ->where('status', AssignmentStatus::Active->value)
            ->update(['status' => $status->value, 'returned_on' => Carbon::now()->toDateString()]);
    }

    /**
     * @param  array{target_type:string, identity_number?:string|null, target_reference?:string|null, target_label?:string|null}  $data
     */
    private function createAssignment(Asset $asset, array $data, ?int $by): AssetAssignment
    {
        $identity = $this->resolveIdentity($data);
        $owner = $identity?->owner;

        return AssetAssignment::query()->create([
            'school_id' => $asset->school_id,
            'asset_id' => $asset->id,
            'target_type' => $data['target_type'],
            'target_label' => $data['target_label'] ?? ($owner?->getAttribute('name') ?? ($data['target_reference'] ?? null)),
            'target_reference' => $data['target_reference'] ?? null,
            'identity_id' => $identity?->id,
            'owner_type' => $owner !== null ? $owner::class : null,
            'owner_id' => $owner?->getKey(),
            'assigned_on' => Carbon::now()->toDateString(),
            'status' => AssignmentStatus::Active->value,
            'assigned_by' => $by,
        ]);
    }

    /**
     * @param  array{identity_number?:string|null}  $data
     */
    private function resolveIdentity(array $data): ?Identity
    {
        if (empty($data['identity_number'])) {
            return null;
        }

        $identity = $this->identities->lookup((string) $data['identity_number']);
        if ($identity === null) {
            throw BusinessRuleException::make('No identity found for this identity number.', 'IDENTITY_NOT_FOUND');
        }

        return $identity;
    }

    private function timeline(AssetAssignment $assignment): void
    {
        $owner = $assignment->owner;
        if ($owner instanceof Staff) {
            $this->staffTimeline->record($owner->id, 'inventory.asset_assigned', 'Asset assigned', null, ['assignment_id' => $assignment->id, 'asset_id' => $assignment->asset_id]);
        } elseif ($owner instanceof Student) {
            $this->studentTimeline->record($owner, 'inventory.asset_assigned', 'Asset assigned', null, ['assignment_id' => $assignment->id, 'asset_id' => $assignment->asset_id]);
        }
    }
}
