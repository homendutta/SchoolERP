<?php

declare(strict_types=1);

use App\Modules\Administration\Models\School;
use App\Modules\Staff\Models\Staff;
use App\Modules\Students\Models\Student;
use App\Platform\Foundation\Identity\Enums\IdentityType;
use App\Platform\Foundation\Identity\IdentityService;
use App\Platform\Foundation\Identity\Models\Identity;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    actingAsSuperAdmin();
});

function makeStaffMember(int $schoolId): Staff
{
    return Staff::create([
        'school_id' => $schoolId,
        'employee_number' => 'E1',
        'name' => 'Ramesh Kumar',
        'status' => 'active',
    ]);
}

// ---------------- Automatic creation ----------------
it('automatically creates a permanent identity for staff on creation', function (): void {
    $staff = makeStaffMember($this->school->id);

    $identity = Identity::where('owner_type', Staff::class)->where('owner_id', $staff->id)->first();
    expect($identity)->not->toBeNull();
    expect($identity->identity_type->value)->toBe('staff');
    expect($staff->fresh()->identity_id)->toBe($identity->id);
});

it('automatically creates an identity for a student on creation', function (): void {
    $student = Student::create([
        'school_id' => $this->school->id, 'admission_number' => '1001', 'name' => 'Asha', 'status' => 'active',
    ]);

    $this->assertDatabaseHas('identities', ['owner_type' => Student::class, 'owner_id' => $student->id, 'identity_type' => 'student']);
    expect($student->fresh()->identity_id)->not->toBeNull();
});

// ---------------- Security: no internal ids exposed ----------------
it('never exposes internal database ids in the public identifier or qr payload', function (): void {
    $staff = makeStaffMember($this->school->id);
    $identity = $staff->identity()->first();

    expect($identity->public_identifier)->not->toBe((string) $identity->id);
    expect($identity->public_identifier)->toStartWith('id_');

    $payload = $identity->qr_payload;
    expect($payload)->toHaveKeys(['identity', 'type', 'school', 'public_id']);
    expect($payload)->not->toHaveKey('id');
    expect($payload)->not->toHaveKey('owner_id');
    expect($payload['public_id'])->toBe($identity->public_identifier);
});

// ---------------- Number from generator + unique ----------------
it('issues a unique identity number from the number generator', function (): void {
    $a = makeStaffMember($this->school->id)->identity()->first();
    $b = Staff::create(['school_id' => $this->school->id, 'employee_number' => 'E2', 'name' => 'Two', 'status' => 'active'])->identity()->first();

    expect($a->identity_number)->not->toBe($b->identity_number);
    $this->assertDatabaseHas('business_number_registry', ['type' => 'identity_number']);
});

// ---------------- Immutability ----------------
it('keeps the identity unchanged when the owner changes role or status', function (): void {
    $staff = makeStaffMember($this->school->id);
    $identity = $staff->identity()->first();
    $number = $identity->identity_number;
    $publicId = $identity->public_identifier;

    // Change employment status + name (role/identity is not affected).
    $staff->forceFill(['status' => 'on_leave', 'name' => 'Renamed'])->save();

    $identity->refresh();
    expect($identity->identity_number)->toBe($number);
    expect($identity->public_identifier)->toBe($publicId);
    expect($identity->owner_id)->toBe($staff->id);
});

it('does not re-issue an identity (idempotent ensure)', function (): void {
    $staff = makeStaffMember($this->school->id);
    $first = $staff->identity()->first()->id;

    app(IdentityService::class)
        ->ensureFor($staff, IdentityType::Staff);

    expect(Identity::where('owner_type', Staff::class)->where('owner_id', $staff->id)->count())->toBe(1);
    expect($staff->identity()->first()->id)->toBe($first);
});

// ---------------- API: show / qr / barcode ----------------
it('returns identity details, a dynamic qr image and a dynamic barcode image', function (): void {
    $identity = makeStaffMember($this->school->id)->identity()->first();

    $this->getJson("/api/v1/identity/{$identity->id}")
        ->assertOk()
        ->assertJsonPath('data.identity_number', $identity->identity_number)
        ->assertJsonPath('data.public_identifier', $identity->public_identifier);

    $qr = $this->get("/api/v1/identity/{$identity->id}/qr");
    $qr->assertOk();
    expect($qr->headers->get('content-type'))->toContain('image/svg+xml');
    expect($qr->getContent())->toContain('<svg');

    $this->getJson("/api/v1/identity/{$identity->id}/qr?format=payload")
        ->assertOk()->assertJsonPath('data.public_id', $identity->public_identifier);

    $barcode = $this->get("/api/v1/identity/{$identity->id}/barcode");
    $barcode->assertOk();
    expect($barcode->getContent())->toContain('<svg');
});

// ---------------- Regenerate (immutable fields preserved) ----------------
it('regenerates derived data without changing immutable fields', function (): void {
    $identity = makeStaffMember($this->school->id)->identity()->first();
    $number = $identity->identity_number;
    $publicId = $identity->public_identifier;

    $this->postJson('/api/v1/identity/regenerate', ['identity_id' => $identity->id])
        ->assertOk()
        ->assertJsonPath('data.identity_number', $number)
        ->assertJsonPath('data.public_identifier', $publicId);

    $this->assertDatabaseHas('activity_logs', ['action' => 'identity.regenerated']);
});

// ---------------- Enable / disable ----------------
it('disables and enables an identity with audit entries', function (): void {
    $identity = makeStaffMember($this->school->id)->identity()->first();

    $this->postJson("/api/v1/identity/{$identity->id}/status", ['status' => 'disabled'])
        ->assertOk()->assertJsonPath('data.status', 'disabled');
    $this->assertDatabaseHas('activity_logs', ['action' => 'identity.disabled']);

    $this->postJson("/api/v1/identity/{$identity->id}/status", ['status' => 'active'])
        ->assertOk()->assertJsonPath('data.status', 'active');
    $this->assertDatabaseHas('activity_logs', ['action' => 'identity.enabled']);
});

// ---------------- Search ----------------
it('searches identities by number, public identifier and owner', function (): void {
    $staff = makeStaffMember($this->school->id);
    $identity = $staff->identity()->first();

    $this->getJson('/api/v1/identity/search?'.http_build_query(['search' => ['identity_number' => $identity->identity_number]]))
        ->assertOk()->assertJsonCount(1, 'data');

    $this->getJson('/api/v1/identity/search?'.http_build_query(['search' => ['owner' => 'Ramesh']]))
        ->assertOk()->assertJsonCount(1, 'data');

    $this->getJson('/api/v1/identity/search?'.http_build_query(['filter' => ['identity_type' => 'staff']]))
        ->assertOk()->assertJsonCount(1, 'data');
});
