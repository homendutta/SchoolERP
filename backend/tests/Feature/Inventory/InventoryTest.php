<?php

declare(strict_types=1);

use App\Modules\Administration\Models\School;
use App\Modules\Inventory\Models\Asset;
use App\Modules\Inventory\Models\AssetAssignment;
use App\Modules\Inventory\Models\AssetCategory;
use App\Modules\Inventory\Models\Consumable;
use App\Modules\Staff\Models\Staff;
use App\Platform\Foundation\Identity\Models\Identity;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    $this->staff = Staff::create(['school_id' => $this->school->id, 'employee_number' => 'E1', 'name' => 'Ramesh', 'status' => 'active']);
    $this->category = AssetCategory::create(['school_id' => $this->school->id, 'name' => 'Computer']);

    actingAsSuperAdmin();
});

// ---------------- Assets (Number Generator + Identity) ----------------
it('creates a physical asset with an auto number and its own permanent identity', function (): void {
    $data = $this->postJson('/api/v1/inventory/assets', [
        'school_id' => $this->school->id, 'category_id' => $this->category->id, 'serial_number' => 'SN-1', 'purchase_value' => 50000,
    ])->assertCreated()->json('data');

    expect($data['asset_number'])->not->toBeNull();
    $asset = Asset::first();
    expect($asset->identity_id)->not->toBeNull();

    $this->getJson("/api/v1/inventory/assets/{$asset->id}")
        ->assertOk()->assertJsonPath('data.assetIdentity.identity_number', Identity::find($asset->identity_id)->identity_number);
});

it('supports parent/child categories', function (): void {
    $child = $this->postJson('/api/v1/inventory/categories', ['school_id' => $this->school->id, 'name' => 'Laptops', 'parent_id' => $this->category->id])
        ->assertCreated()->json('data');
    expect($child['parent_id'])->toBe($this->category->id);
});

// ---------------- Consumables never get Identity ----------------
it('creates a consumable without an identity and never mixes with assets', function (): void {
    $this->postJson('/api/v1/inventory/consumables', ['school_id' => $this->school->id, 'name' => 'Printer Paper', 'unit' => 'ream', 'minimum_stock' => 5])
        ->assertCreated()->assertJsonPath('data.name', 'Printer Paper');

    $consumable = Consumable::first();
    expect($consumable->getAttributes())->not->toHaveKey('identity_id');
});

// ---------------- Stock movements (append-only) + low stock ----------------
it('records append-only stock movements and publishes a low-stock event', function (): void {
    $consumable = Consumable::create(['school_id' => $this->school->id, 'name' => 'Chalk', 'unit' => 'box', 'minimum_stock' => 10, 'current_stock' => 0]);

    $this->postJson('/api/v1/inventory/movements', ['consumable_id' => $consumable->id, 'type' => 'in', 'quantity' => 50])
        ->assertCreated()->assertJsonPath('data.balance_after', 50);
    $this->postJson('/api/v1/inventory/movements', ['consumable_id' => $consumable->id, 'type' => 'out', 'quantity' => 45])
        ->assertCreated()->assertJsonPath('data.balance_after', 5);

    // Two movements kept (append-only); consumable balance updated to 5.
    expect($consumable->fresh()->current_stock)->toBe(5.0);
    $this->assertDatabaseCount('stock_movements', 2);
    // Below minimum → low-stock communication published.
    $this->assertDatabaseHas('communication_batches', ['event' => 'inventory.low_stock']);
});

// ---------------- Asset assignment (historical) ----------------
it('assigns an asset to staff, marks it assigned, and writes history', function (): void {
    $asset = makeAsset();
    $identityNumber = Identity::find($this->staff->identity_id)->identity_number;

    $this->postJson('/api/v1/inventory/assignments', [
        'asset_id' => $asset->id, 'target_type' => 'staff', 'identity_number' => $identityNumber, 'target_label' => 'Ramesh',
    ])->assertCreated()->assertJsonPath('data.status', 'active');

    expect($asset->fresh()->status->value)->toBe('assigned');
    $this->assertDatabaseHas('activity_logs', ['action' => 'inventory.asset_assigned']);
    $this->assertDatabaseHas('staff_timelines', ['staff_id' => $this->staff->id, 'event_type' => 'inventory.asset_assigned']);
    $this->assertDatabaseHas('communication_batches', ['event' => 'inventory.asset_assigned']);
});

it('transfers an asset to a new target, preserving assignment history', function (): void {
    $asset = makeAsset();
    $identityNumber = Identity::find($this->staff->identity_id)->identity_number;

    $this->postJson('/api/v1/inventory/assignments', ['asset_id' => $asset->id, 'target_type' => 'staff', 'identity_number' => $identityNumber, 'target_label' => 'Ramesh'])->assertCreated();
    $this->postJson('/api/v1/inventory/transfers', ['asset_id' => $asset->id, 'target_type' => 'room', 'target_reference' => 'ROOM-LAB-1', 'target_label' => 'Lab 1', 'transfer_type' => 'room'])->assertCreated();

    expect(AssetAssignment::where('asset_id', $asset->id)->count())->toBe(2);
    expect(AssetAssignment::where('asset_id', $asset->id)->where('status', 'active')->count())->toBe(1);
    expect(AssetAssignment::where('asset_id', $asset->id)->where('status', 'transferred')->count())->toBe(1);
    $this->assertDatabaseHas('asset_transfers', ['asset_id' => $asset->id]);
});

it('returns an assigned asset and frees it', function (): void {
    $asset = makeAsset();
    $identityNumber = Identity::find($this->staff->identity_id)->identity_number;
    $assignmentId = $this->postJson('/api/v1/inventory/assignments', ['asset_id' => $asset->id, 'target_type' => 'staff', 'identity_number' => $identityNumber])->json('data.id');

    $this->postJson("/api/v1/inventory/assignments/{$assignmentId}/return")->assertOk()->assertJsonPath('data.status', 'returned');
    expect($asset->fresh()->status->value)->toBe('available');
});

// ---------------- Verification + disposal (historical) ----------------
it('records verification history and reports outcomes', function (): void {
    $asset = makeAsset();

    $this->postJson('/api/v1/inventory/verifications', ['asset_id' => $asset->id, 'status' => 'verified'])->assertCreated();
    $this->getJson("/api/v1/inventory/verifications/report?school_id={$this->school->id}")
        ->assertOk()->assertJsonPath('data.verified', 1);
});

it('disposes an asset without deleting it', function (): void {
    $asset = makeAsset();

    $this->postJson('/api/v1/inventory/disposals', ['asset_id' => $asset->id, 'method' => 'scrapped', 'value' => 100])
        ->assertCreated()->assertJsonPath('data.method', 'scrapped');

    expect($asset->fresh()->status->value)->toBe('disposed');
    expect(Asset::withTrashed()->count())->toBe(1); // never deleted
    $this->assertDatabaseHas('activity_logs', ['action' => 'inventory.disposed']);
});

// ---------------- Asset lifecycle (state machine) ----------------
it('records a lifecycle timeline and exposes the current state through the API', function (): void {
    // Creating through the service records the initial lifecycle event.
    $assetId = $this->postJson('/api/v1/inventory/assets', [
        'school_id' => $this->school->id, 'category_id' => $this->category->id, 'serial_number' => 'SN-LC',
    ])->assertCreated()->json('data.id');

    $this->assertDatabaseHas('asset_lifecycle_events', ['asset_id' => $assetId, 'to_status' => 'available']);

    $this->postJson("/api/v1/inventory/assets/{$assetId}/lifecycle", ['to_status' => 'in_maintenance', 'note' => 'Sent to repair'])
        ->assertOk()->assertJsonPath('data.status', 'in_maintenance');

    expect(Asset::find($assetId)->status->value)->toBe('in_maintenance');
    $this->assertDatabaseHas('activity_logs', ['action' => 'inventory.lifecycle_transition']);

    $this->getJson("/api/v1/inventory/assets/{$assetId}/lifecycle")
        ->assertOk()->assertJsonPath('data.1.to_status', 'in_maintenance');
});

// ---------------- Maintenance + warranty ----------------
it('schedules maintenance and records a warranty', function (): void {
    $asset = makeAsset();

    $this->postJson('/api/v1/inventory/maintenance', ['school_id' => $this->school->id, 'asset_id' => $asset->id, 'type' => 'preventive', 'priority' => 'high', 'scheduled_date' => now()->addDays(5)->toDateString()])
        ->assertCreated()->assertJsonPath('data.type', 'preventive');

    $this->postJson('/api/v1/inventory/warranties', ['school_id' => $this->school->id, 'asset_id' => $asset->id, 'start_date' => now()->toDateString(), 'end_date' => now()->addYear()->toDateString(), 'coverage' => 'Full'])
        ->assertCreated()->assertJsonPath('data.status', 'active');
});

// ---------------- Search + dashboard ----------------
it('searches assets and returns the dashboard', function (): void {
    makeAsset('SN-XYZ');

    $this->getJson('/api/v1/inventory/assets?'.http_build_query(['search' => ['serial_number' => 'SN-XYZ']]))
        ->assertOk()->assertJsonCount(1, 'data');

    $this->getJson("/api/v1/inventory/dashboard?school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['total_assets', 'active_assets', 'assigned_assets', 'consumables', 'low_stock', 'warranty_expiring', 'maintenance_due', 'verification_pending'],
            'charts' => ['category_distribution', 'asset_allocation', 'maintenance_trend', 'stock_consumption'],
        ]]);
});

/** Helper: a physical asset (with its own Identity). */
function makeAsset(string $serial = 'SN-1'): Asset
{
    $asset = Asset::create(['school_id' => test()->school->id, 'asset_number' => 'A'.uniqid(), 'category_id' => test()->category->id, 'serial_number' => $serial]);

    return $asset->refresh();
}
