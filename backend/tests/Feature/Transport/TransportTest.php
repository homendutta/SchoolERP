<?php

declare(strict_types=1);

use App\Modules\Administration\Models\School;
use App\Modules\Staff\Models\Staff;
use App\Modules\Students\Models\Student;
use App\Modules\Transport\Models\Stop;
use App\Modules\Transport\Models\StudentAssignment;
use App\Modules\Transport\Models\TransportRoute;
use App\Modules\Transport\Models\Trip;
use App\Modules\Transport\Models\Vehicle;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    $this->student = Student::create(['school_id' => $this->school->id, 'admission_number' => '1001', 'name' => 'Asha', 'status' => 'active']);
    $this->student2 = Student::create(['school_id' => $this->school->id, 'admission_number' => '1002', 'name' => 'Bina', 'status' => 'active']);
    $this->driver = Staff::create(['school_id' => $this->school->id, 'employee_number' => 'D1', 'name' => 'Ravi', 'status' => 'active']);

    actingAsSuperAdmin();
});

// ---------------- Vehicles (Number Generator) ----------------
it('creates a vehicle with an auto-generated vehicle number', function (): void {
    $data = $this->postJson('/api/v1/transport/vehicles', [
        'school_id' => $this->school->id, 'registration_number' => 'KA01AB1234', 'seating_capacity' => 40, 'vehicle_type' => 'bus',
    ])->assertCreated()->json('data');

    expect($data['vehicle_number'])->not->toBeNull();
    expect($data['status'])->toBe('active');
});

// ---------------- Routes (Number Generator) + stops ----------------
it('creates a route with an auto-generated code and ordered stops', function (): void {
    $routeId = $this->postJson('/api/v1/transport/routes', ['school_id' => $this->school->id, 'name' => 'North Line'])
        ->assertCreated()->json('data.route_code') !== null
        ? TransportRoute::first()->id : null;
    expect($routeId)->not->toBeNull();

    $this->postJson('/api/v1/transport/stops', ['school_id' => $this->school->id, 'route_id' => $routeId, 'name' => 'Gate A', 'sequence' => 1])->assertCreated();
    $this->postJson('/api/v1/transport/stops', ['school_id' => $this->school->id, 'route_id' => $routeId, 'name' => 'Gate B', 'sequence' => 2])->assertCreated();

    $this->getJson("/api/v1/transport/routes/{$routeId}")->assertOk()->assertJsonCount(2, 'data.stops');
});

// ---------------- Drivers from Staff ----------------
it('assigns a driver (Staff) to a vehicle', function (): void {
    $vehicle = Vehicle::create(['school_id' => $this->school->id, 'vehicle_number' => 'V1', 'seating_capacity' => 40]);

    $this->postJson('/api/v1/transport/drivers', [
        'school_id' => $this->school->id, 'vehicle_id' => $vehicle->id, 'staff_id' => $this->driver->id, 'role' => 'primary_driver',
    ])->assertCreated()->assertJsonPath('data.role', 'primary_driver');
});

// ---------------- Student assignment (route+stop, never vehicle) ----------------
it('assigns a student to a route and stop, never to a vehicle', function (): void {
    [$route, $stop] = makeRouteWithTrip(40);

    $this->postJson('/api/v1/transport/students', [
        'student_id' => $this->student->id, 'route_id' => $route->id, 'stop_id' => $stop->id,
    ])->assertCreated()->assertJsonPath('data.status', 'active');

    $assignment = StudentAssignment::first();
    expect($assignment->route_id)->toBe($route->id);
    expect($assignment->stop_id)->toBe($stop->id);
    // No vehicle column exists on the assignment — vehicle is via the trip.
    expect(array_key_exists('vehicle_id', $assignment->getAttributes()))->toBeFalse();
    $this->assertDatabaseHas('activity_logs', ['action' => 'transport.student_assigned']);
    $this->assertDatabaseHas('student_timelines', ['student_id' => $this->student->id, 'event_type' => 'transport.assigned']);
    // Assignment publishes a communication event (never sent directly by Transport).
    $this->assertDatabaseHas('communication_batches', ['event' => 'transport.route_changed']);
});

it('rejects a stop that does not belong to the route', function (): void {
    [$route] = makeRouteWithTrip(40);
    $otherRoute = TransportRoute::create(['school_id' => $this->school->id, 'route_code' => 'R9', 'name' => 'Other']);
    $otherStop = Stop::create(['school_id' => $this->school->id, 'route_id' => $otherRoute->id, 'name' => 'X', 'sequence' => 1]);

    $this->postJson('/api/v1/transport/students', [
        'student_id' => $this->student->id, 'route_id' => $route->id, 'stop_id' => $otherStop->id,
    ])->assertStatus(422)->assertJsonPath('code', 'STOP_ROUTE_MISMATCH');
});

// ---------------- Capacity enforcement ----------------
it('enforces vehicle capacity and never over-allocates', function (): void {
    [$route, $stop] = makeRouteWithTrip(1); // one seat only

    $this->postJson('/api/v1/transport/students', ['student_id' => $this->student->id, 'route_id' => $route->id, 'stop_id' => $stop->id])->assertCreated();
    $this->postJson('/api/v1/transport/students', ['student_id' => $this->student2->id, 'route_id' => $route->id, 'stop_id' => $stop->id])
        ->assertStatus(422)->assertJsonPath('code', 'OVER_CAPACITY');
});

it('preserves history when a student is re-assigned', function (): void {
    [$route, $stop] = makeRouteWithTrip(40);
    [$route2, $stop2] = makeRouteWithTrip(40);

    $this->postJson('/api/v1/transport/students', ['student_id' => $this->student->id, 'route_id' => $route->id, 'stop_id' => $stop->id])->assertCreated();
    $this->postJson('/api/v1/transport/students', ['student_id' => $this->student->id, 'route_id' => $route2->id, 'stop_id' => $stop2->id])->assertCreated();

    // Old assignment kept as history (transferred), new one active.
    expect(StudentAssignment::where('student_id', $this->student->id)->count())->toBe(2);
    expect(StudentAssignment::where('student_id', $this->student->id)->where('status', 'active')->count())->toBe(1);
    expect(StudentAssignment::where('student_id', $this->student->id)->where('status', 'transferred')->count())->toBe(1);
});

// ---------------- Trips ----------------
it('creates a scheduled trip on a route with a vehicle and driver', function (): void {
    $vehicle = Vehicle::create(['school_id' => $this->school->id, 'vehicle_number' => 'V1', 'seating_capacity' => 40]);
    $route = TransportRoute::create(['school_id' => $this->school->id, 'route_code' => 'R1', 'name' => 'Line 1']);

    $this->postJson('/api/v1/transport/trips', [
        'school_id' => $this->school->id, 'vehicle_id' => $vehicle->id, 'route_id' => $route->id,
        'driver_id' => $this->driver->id, 'shift' => 'morning',
    ])->assertCreated()->assertJsonPath('data.status', 'scheduled');
});

// ---------------- Fees (Finance manages payment) + maintenance ----------------
it('defines a transport fee (collection handled by Finance)', function (): void {
    $route = TransportRoute::create(['school_id' => $this->school->id, 'route_code' => 'R1', 'name' => 'Line 1']);

    $this->postJson('/api/v1/transport/fees', [
        'school_id' => $this->school->id, 'fee_type' => 'route', 'route_id' => $route->id, 'name' => 'North route fee', 'amount' => 1200,
    ])->assertCreated()->assertJsonPath('data.fee_type', 'route');
});

it('schedules vehicle maintenance', function (): void {
    $vehicle = Vehicle::create(['school_id' => $this->school->id, 'vehicle_number' => 'V1', 'seating_capacity' => 40]);

    $this->postJson('/api/v1/transport/maintenance', [
        'school_id' => $this->school->id, 'vehicle_id' => $vehicle->id, 'service_type' => 'Oil change',
        'service_due_date' => now()->addDays(3)->toDateString(),
    ])->assertCreated();
});

// ---------------- Search + dashboard ----------------
it('searches vehicles and returns the dashboard', function (): void {
    Vehicle::create(['school_id' => $this->school->id, 'vehicle_number' => 'V1', 'registration_number' => 'KA01AB1234', 'seating_capacity' => 40]);

    $this->getJson('/api/v1/transport/vehicles?'.http_build_query(['search' => ['registration' => 'KA01']]))
        ->assertOk()->assertJsonCount(1, 'data');

    $this->getJson("/api/v1/transport/dashboard?school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['vehicles', 'routes', 'trips', 'assigned_students', 'drivers', 'over_capacity', 'maintenance_due'],
            'charts' => ['route_usage', 'vehicle_utilization', 'student_distribution', 'capacity_usage'],
        ]]);
});

/** Helper: a route + stop backed by a scheduled trip with a vehicle of $seats seats. */
function makeRouteWithTrip(int $seats): array
{
    $route = TransportRoute::create(['school_id' => test()->school->id, 'route_code' => 'R'.uniqid(), 'name' => 'Route '.uniqid()]);
    $stop = Stop::create(['school_id' => test()->school->id, 'route_id' => $route->id, 'name' => 'Stop', 'sequence' => 1]);
    $vehicle = Vehicle::create(['school_id' => test()->school->id, 'vehicle_number' => 'V'.uniqid(), 'seating_capacity' => $seats]);
    Trip::create(['school_id' => test()->school->id, 'vehicle_id' => $vehicle->id, 'route_id' => $route->id, 'shift' => 'morning', 'status' => 'scheduled']);

    return [$route, $stop];
}
