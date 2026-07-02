<?php

declare(strict_types=1);

use App\Modules\Administration\Models\School;
use App\Modules\Hostel\Models\Allocation;
use App\Modules\Hostel\Models\Bed;
use App\Modules\Hostel\Models\Building;
use App\Modules\Hostel\Models\Floor;
use App\Modules\Hostel\Models\Hostel;
use App\Modules\Hostel\Models\Room;
use App\Modules\Staff\Models\Staff;
use App\Modules\Students\Models\Student;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    $this->student = Student::create(['school_id' => $this->school->id, 'admission_number' => '1001', 'name' => 'Asha', 'status' => 'active']);
    $this->student2 = Student::create(['school_id' => $this->school->id, 'admission_number' => '1002', 'name' => 'Bina', 'status' => 'active']);
    $this->warden = Staff::create(['school_id' => $this->school->id, 'employee_number' => 'W1', 'name' => 'Mr Warden', 'status' => 'active']);

    actingAsSuperAdmin();
});

/** Helper: hostel → building → floor → room (capacity) → returns [hostel, room]. */
function makeRoom(int $capacity = 2): array
{
    $hostel = Hostel::create(['school_id' => test()->school->id, 'code' => 'H'.uniqid(), 'name' => 'Hostel A', 'gender' => 'boys']);
    $building = Building::create(['school_id' => test()->school->id, 'hostel_id' => $hostel->id, 'name' => 'Block 1']);
    $floor = Floor::create(['school_id' => test()->school->id, 'building_id' => $building->id, 'floor_number' => 1]);
    $room = Room::create(['school_id' => test()->school->id, 'hostel_id' => $hostel->id, 'building_id' => $building->id, 'floor_id' => $floor->id, 'room_number' => 'R'.uniqid(), 'capacity' => $capacity]);

    return [$hostel, $room];
}

// ---------------- Hostels (Number Generator) ----------------
it('creates a hostel with an auto-generated code', function (): void {
    $data = $this->postJson('/api/v1/hostel/hostels', ['school_id' => $this->school->id, 'name' => 'Boys Hostel', 'gender' => 'boys'])
        ->assertCreated()->json('data');
    expect($data['code'])->not->toBeNull();
});

// ---------------- Beds (Number Generator + room capacity) ----------------
it('creates beds up to room capacity and then blocks over-capacity', function (): void {
    [, $room] = makeRoom(1);

    $bed = $this->postJson('/api/v1/hostel/beds', ['school_id' => $this->school->id, 'room_id' => $room->id, 'bed_number' => 'B1'])
        ->assertCreated()->json('data');
    expect($bed['bed_code'])->not->toBeNull();

    $this->postJson('/api/v1/hostel/beds', ['school_id' => $this->school->id, 'room_id' => $room->id, 'bed_number' => 'B2'])
        ->assertStatus(422)->assertJsonPath('code', 'ROOM_CAPACITY');
});

// ---------------- Allocation (bed, single-occupant) ----------------
it('allocates a student to a bed, never a room, and marks the bed occupied', function (): void {
    [, $room] = makeRoom(2);
    $bed = Bed::create(['school_id' => $this->school->id, 'room_id' => $room->id, 'bed_number' => 'B1', 'bed_code' => 'BC1']);

    $this->postJson('/api/v1/hostel/allocations', ['student_id' => $this->student->id, 'bed_id' => $bed->id])
        ->assertCreated()->assertJsonPath('data.status', 'active');

    expect($bed->fresh()->status->value)->toBe('occupied');
    $this->assertDatabaseHas('activity_logs', ['action' => 'hostel.allocated']);
    $this->assertDatabaseHas('student_timelines', ['student_id' => $this->student->id, 'event_type' => 'hostel.allocated']);
    $this->assertDatabaseHas('communication_batches', ['event' => 'hostel.allocation']);
});

it('never lets a bed have two active occupants', function (): void {
    [, $room] = makeRoom(2);
    $bed = Bed::create(['school_id' => $this->school->id, 'room_id' => $room->id, 'bed_number' => 'B1', 'bed_code' => 'BC1']);

    $this->postJson('/api/v1/hostel/allocations', ['student_id' => $this->student->id, 'bed_id' => $bed->id])->assertCreated();
    $this->postJson('/api/v1/hostel/allocations', ['student_id' => $this->student2->id, 'bed_id' => $bed->id])
        ->assertStatus(422)->assertJsonPath('code', 'BED_UNAVAILABLE');
});

// ---------------- Transfers (history preserved) ----------------
it('transfers a student to a new bed, freeing the old bed and keeping history', function (): void {
    [, $room] = makeRoom(2);
    $bed1 = Bed::create(['school_id' => $this->school->id, 'room_id' => $room->id, 'bed_number' => 'B1', 'bed_code' => 'BC1']);
    $bed2 = Bed::create(['school_id' => $this->school->id, 'room_id' => $room->id, 'bed_number' => 'B2', 'bed_code' => 'BC2']);

    $this->postJson('/api/v1/hostel/allocations', ['student_id' => $this->student->id, 'bed_id' => $bed1->id])->assertCreated();
    $this->postJson('/api/v1/hostel/transfers', ['student_id' => $this->student->id, 'to_bed_id' => $bed2->id, 'reason' => 'Room change'])
        ->assertCreated();

    expect($bed1->fresh()->status->value)->toBe('available'); // freed
    expect($bed2->fresh()->status->value)->toBe('occupied');
    // Old allocation kept as history (transferred), new one active.
    expect(Allocation::where('student_id', $this->student->id)->count())->toBe(2);
    expect(Allocation::where('student_id', $this->student->id)->where('status', 'active')->count())->toBe(1);
    expect(Allocation::where('student_id', $this->student->id)->where('status', 'transferred')->count())->toBe(1);
    $this->assertDatabaseHas('hostel_transfers', ['student_id' => $this->student->id]);
    $this->assertDatabaseHas('student_timelines', ['student_id' => $this->student->id, 'event_type' => 'hostel.transferred']);
});

it('checks out a student and frees the bed', function (): void {
    [, $room] = makeRoom(2);
    $bed = Bed::create(['school_id' => $this->school->id, 'room_id' => $room->id, 'bed_number' => 'B1', 'bed_code' => 'BC1']);
    $allocId = $this->postJson('/api/v1/hostel/allocations', ['student_id' => $this->student->id, 'bed_id' => $bed->id])->json('data.id');

    $this->postJson("/api/v1/hostel/allocations/{$allocId}/checkout")->assertOk()->assertJsonPath('data.status', 'checked_out');
    expect($bed->fresh()->status->value)->toBe('available');
});

// ---------------- Wardens (Staff) ----------------
it('assigns a warden (Staff) to a hostel', function (): void {
    [$hostel] = makeRoom();

    $this->postJson('/api/v1/hostel/wardens', ['school_id' => $this->school->id, 'hostel_id' => $hostel->id, 'staff_id' => $this->warden->id, 'role' => 'chief'])
        ->assertCreated()->assertJsonPath('data.role', 'chief');
});

// ---------------- Visitors + maintenance + fees ----------------
it('records a visitor, a maintenance request, and a hostel fee', function (): void {
    [$hostel, $room] = makeRoom();

    $this->postJson('/api/v1/hostel/visitors', ['school_id' => $this->school->id, 'student_id' => $this->student->id, 'visitor_name' => 'Parent', 'purpose' => 'Visit'])
        ->assertCreated()->assertJsonPath('data.status', 'pending');

    $this->postJson('/api/v1/hostel/maintenance', ['school_id' => $this->school->id, 'hostel_id' => $hostel->id, 'room_id' => $room->id, 'category' => 'plumbing', 'priority' => 'high', 'description' => 'Leak'])
        ->assertCreated()->assertJsonPath('data.category', 'plumbing');

    $this->postJson('/api/v1/hostel/fees', ['school_id' => $this->school->id, 'hostel_id' => $hostel->id, 'fee_type' => 'mess', 'name' => 'Mess fee', 'amount' => 3000])
        ->assertCreated()->assertJsonPath('data.fee_type', 'mess');
});

// ---------------- Occupancy + dashboard ----------------
it('reports occupancy and returns the dashboard', function (): void {
    [$hostel, $room] = makeRoom(2);
    $bed = Bed::create(['school_id' => $this->school->id, 'room_id' => $room->id, 'bed_number' => 'B1', 'bed_code' => 'BC1']);
    Bed::create(['school_id' => $this->school->id, 'room_id' => $room->id, 'bed_number' => 'B2', 'bed_code' => 'BC2']);
    $this->postJson('/api/v1/hostel/allocations', ['student_id' => $this->student->id, 'bed_id' => $bed->id])->assertCreated();

    $this->getJson("/api/v1/hostel/occupancy?school_id={$this->school->id}")
        ->assertOk()->assertJsonPath('data.occupied', 1)->assertJsonPath('data.available', 1)->assertJsonPath('data.total', 2);

    $this->getJson("/api/v1/hostel/dashboard?school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['hostels', 'buildings', 'rooms', 'beds', 'occupied', 'available', 'visitors_today', 'pending_maintenance'],
            'charts' => ['occupancy', 'hostel_distribution', 'maintenance_trend', 'student_allocation'],
        ]]);
});
