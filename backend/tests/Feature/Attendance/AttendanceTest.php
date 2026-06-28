<?php

declare(strict_types=1);

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Administration\Models\School;
use App\Modules\Attendance\Models\AttendanceRecord;
use App\Modules\Attendance\Models\BiometricDevice;
use App\Modules\Attendance\Models\BiometricLog;
use App\Modules\Staff\Models\Staff;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;
use App\Platform\Foundation\Identity\Models\Identity;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    $this->year = AcademicYear::create(['school_id' => $this->school->id, 'name' => '2025-2026', 'slug' => '2025-2026', 'start_date' => '2025-04-01', 'end_date' => '2026-03-31', 'status' => 'active']);
    $this->class = SchoolClass::create(['school_id' => $this->school->id, 'code' => 'C1', 'name' => 'Grade 1', 'slug' => 'grade-1', 'status' => 'active']);
    $this->section = Section::create(['class_id' => $this->class->id, 'name' => 'A', 'capacity' => 40, 'status' => 'active']);

    $this->student = Student::create(['school_id' => $this->school->id, 'admission_number' => '1001', 'name' => 'Asha', 'status' => 'active']);
    StudentAcademicRecord::create([
        'school_id' => $this->school->id, 'student_id' => $this->student->id, 'academic_year_id' => $this->year->id,
        'class_id' => $this->class->id, 'section_id' => $this->section->id, 'status' => 'active', 'is_current' => true, 'started_on' => now()->toDateString(),
    ]);
    $this->student->refresh();
    $this->studentIdentity = Identity::find($this->student->identity_id);

    $this->staff = Staff::create(['school_id' => $this->school->id, 'employee_number' => 'E1', 'name' => 'Ramesh', 'status' => 'active']);
    $this->staff->refresh();

    actingAsSuperAdmin();
});

// ---------------- Manual marking ----------------
it('marks attendance manually and records identity, audit and timeline', function (): void {
    $this->postJson('/api/v1/attendance/manual', [
        'school_id' => $this->school->id,
        'date' => '2026-06-01',
        'entries' => [['identity_id' => $this->studentIdentity->id, 'status' => 'present']],
    ])->assertCreated()->assertJsonPath('data.marked', 1);

    $record = AttendanceRecord::first();
    expect($record->identity_id)->toBe($this->studentIdentity->id);
    expect($record->owner_type)->toBe(Student::class);
    expect($record->class_id)->toBe($this->class->id); // owner context denormalized
    expect($record->source->value)->toBe('manual');

    $this->assertDatabaseHas('activity_logs', ['action' => 'attendance.marked']);
    $this->assertDatabaseHas('student_timelines', ['student_id' => $this->student->id, 'event_type' => 'attendance.marked']);
});

it('prevents duplicate attendance for the same identity, date and session', function (): void {
    $payload = [
        'school_id' => $this->school->id, 'date' => '2026-06-01',
        'entries' => [['identity_id' => $this->studentIdentity->id, 'status' => 'present']],
    ];
    $this->postJson('/api/v1/attendance/manual', $payload)->assertCreated()->assertJsonPath('data.marked', 1);
    $this->postJson('/api/v1/attendance/manual', $payload)->assertCreated()->assertJsonPath('data.marked', 0)->assertJsonPath('data.skipped', 1);

    expect(AttendanceRecord::count())->toBe(1);
});

it('corrects an existing record through the authorized workflow', function (): void {
    $this->postJson('/api/v1/attendance/manual', [
        'school_id' => $this->school->id, 'date' => '2026-06-01',
        'entries' => [['identity_id' => $this->studentIdentity->id, 'status' => 'present']],
    ])->assertCreated();
    $record = AttendanceRecord::first();

    $this->putJson("/api/v1/attendance/manual/{$record->id}", ['status' => 'absent', 'remarks' => 'Sick'])
        ->assertOk()->assertJsonPath('data.status', 'absent');

    $this->assertDatabaseHas('activity_logs', ['action' => 'attendance.corrected']);
    $this->assertDatabaseHas('student_timelines', ['student_id' => $this->student->id, 'event_type' => 'attendance.corrected']);
});

// ---------------- Biometric ----------------
it('ingests a biometric event by identity number and marks attendance', function (): void {
    $device = BiometricDevice::create(['school_id' => $this->school->id, 'name' => 'Gate', 'device_identifier' => 'ESSL-001']);

    $this->postJson('/api/v1/attendance/biometric/events', [
        'school_id' => $this->school->id,
        'device_identifier' => 'ESSL-001',
        'identity_number' => $this->studentIdentity->identity_number,
        'event_time' => '2026-06-02 09:01:00',
        'direction' => 'in',
    ])->assertCreated()->assertJsonPath('data.processing_status', 'processed');

    $record = AttendanceRecord::where('source', 'biometric')->first();
    expect($record)->not->toBeNull();
    expect($record->identity_id)->toBe($this->studentIdentity->id);
    expect((string) $record->check_in_time)->toContain('09:01');
    expect($device->fresh()->last_sync_at)->not->toBeNull();
});

it('records an unmatched biometric log when the identity number is unknown', function (): void {
    $this->postJson('/api/v1/attendance/biometric/events', [
        'school_id' => $this->school->id,
        'identity_number' => '999999',
        'event_time' => '2026-06-02 09:01:00',
        'direction' => 'in',
    ])->assertCreated()->assertJsonPath('data.processing_status', 'unmatched');

    expect(AttendanceRecord::count())->toBe(0);
    $this->assertDatabaseHas('biometric_logs', ['identity_number' => '999999', 'processing_status' => 'unmatched']);
});

it('ingests a raw eSSL payload through the vendor connector', function (): void {
    BiometricDevice::create(['school_id' => $this->school->id, 'name' => 'Gate', 'device_identifier' => 'ESSL-001', 'device_type' => 'essl_mb20']);

    $this->postJson('/api/v1/attendance/biometric/events', [
        'school_id' => $this->school->id,
        'vendor' => 'essl_mb20',
        'device_identifier' => 'ESSL-001',
        'payload' => ['records' => [
            ['user_id' => $this->studentIdentity->identity_number, 'time' => '2026-06-03 08:55:00', 'inout' => 0],
        ]],
    ])->assertCreated()->assertJsonPath('data.processed', 1);

    expect(AttendanceRecord::where('source', 'biometric')->count())->toBe(1);
});

it('keeps biometric logs and exposes them read-only', function (): void {
    $this->postJson('/api/v1/attendance/biometric/events', [
        'school_id' => $this->school->id, 'identity_number' => $this->studentIdentity->identity_number,
        'event_time' => '2026-06-02 09:01:00', 'direction' => 'in',
    ])->assertCreated();

    $this->getJson("/api/v1/attendance/biometric/logs?school_id={$this->school->id}")
        ->assertOk()->assertJsonCount(1, 'data');
    expect(BiometricLog::count())->toBe(1);
});

// ---------------- Import ----------------
it('imports attendance through the import engine', function (): void {
    $rows = [
        ['school_id' => $this->school->id, 'identity_number' => $this->studentIdentity->identity_number, 'date' => '2026-06-05', 'status' => 'present'],
        ['school_id' => $this->school->id, 'identity_number' => $this->studentIdentity->identity_number, 'date' => 'bad-date', 'status' => 'present'],
        ['school_id' => $this->school->id, 'identity_number' => '999999', 'date' => '2026-06-05', 'status' => 'present'],
    ];

    $this->postJson('/api/v1/attendance/import/validate', ['rows' => $rows])
        ->assertOk()->assertJsonPath('data.valid', false);

    $this->postJson('/api/v1/attendance/import/execute', ['rows' => $rows])
        ->assertOk()->assertJsonPath('data.marked', 1)->assertJsonPath('data.unmatched', 1);
});

// ---------------- Devices ----------------
it('manages biometric devices', function (): void {
    $id = $this->postJson('/api/v1/attendance/devices', [
        'school_id' => $this->school->id, 'name' => 'Main Gate', 'device_identifier' => 'ESSL-XYZ', 'location' => 'Entrance',
    ])->assertCreated()->json('data.id');

    $this->getJson('/api/v1/attendance/devices')->assertOk()->assertJsonCount(1, 'data');
    $this->putJson("/api/v1/attendance/devices/{$id}", ['status' => 'inactive'])->assertOk()->assertJsonPath('data.status', 'inactive');
});

// ---------------- Read + search + dashboard ----------------
it('lists and searches student attendance scoped to student owners', function (): void {
    $this->postJson('/api/v1/attendance/manual', [
        'school_id' => $this->school->id, 'date' => '2026-06-01',
        'entries' => [['identity_id' => $this->studentIdentity->id, 'status' => 'present']],
    ])->assertCreated();

    $this->getJson('/api/v1/attendance/student')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/attendance/staff')->assertOk()->assertJsonCount(0, 'data');
    $this->getJson('/api/v1/attendance/student?'.http_build_query(['search' => ['status' => 'present']]))
        ->assertOk()->assertJsonCount(1, 'data');
});

it('returns the attendance dashboard widgets and charts', function (): void {
    $this->postJson('/api/v1/attendance/manual', [
        'school_id' => $this->school->id, 'date' => now()->toDateString(),
        'entries' => [['identity_id' => $this->studentIdentity->id, 'status' => 'present']],
    ])->assertCreated();

    $this->getJson("/api/v1/attendance/dashboard?type=student&school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['present', 'absent', 'late', 'leave', 'attendance_percentage'],
            'charts' => ['daily', 'weekly', 'monthly'],
        ]]);
});
