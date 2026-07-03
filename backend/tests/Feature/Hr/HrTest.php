<?php

declare(strict_types=1);

use App\Modules\Administration\Models\School;
use App\Modules\HumanResources\Models\EmploymentRecord;
use App\Modules\HumanResources\Models\LeaveBalance;
use App\Modules\Staff\Models\Staff;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    $this->staff = Staff::create(['school_id' => $this->school->id, 'employee_number' => 'E1', 'name' => 'Ramesh', 'status' => 'active']);
    $this->manager = Staff::create(['school_id' => $this->school->id, 'employee_number' => 'E2', 'name' => 'Sunita', 'status' => 'active']);

    actingAsSuperAdmin();
});

// ---------------- Departments + Designations (Number Generator + hierarchy) ----------------
it('creates a department with an auto-generated code and supports parent/child', function (): void {
    $parent = $this->postJson('/api/v1/hr/departments', ['school_id' => $this->school->id, 'name' => 'Administration'])
        ->assertCreated()->json('data');
    expect($parent['code'])->not->toBeNull();

    $child = $this->postJson('/api/v1/hr/departments', ['school_id' => $this->school->id, 'name' => 'Accounts', 'parent_id' => $parent['id']])
        ->assertCreated()->json('data');
    expect($child['parent_id'])->toBe($parent['id']);
});

it('creates a designation with a department, grade and auto code', function (): void {
    $dept = $this->postJson('/api/v1/hr/departments', ['school_id' => $this->school->id, 'name' => 'Teaching'])->json('data');

    $this->postJson('/api/v1/hr/designations', [
        'school_id' => $this->school->id, 'department_id' => $dept['id'], 'name' => 'Senior Teacher', 'grade' => 'G3',
    ])->assertCreated()->assertJsonPath('data.grade', 'G3')
        ->assertJsonPath('data.department_id', $dept['id']);
});

// ---------------- Employment history (never overwritten) ----------------
it('creates employment records and never overwrites history on change', function (): void {
    $this->postJson('/api/v1/hr/employment', [
        'school_id' => $this->school->id, 'staff_id' => $this->staff->id, 'employment_type' => 'full_time',
        'joining_date' => '2020-01-01', 'status' => 'active',
    ])->assertCreated()->assertJsonPath('data.is_current', true);

    // A change creates a NEW record and closes the previous one.
    $this->postJson('/api/v1/hr/employment', [
        'school_id' => $this->school->id, 'staff_id' => $this->staff->id, 'employment_type' => 'full_time',
        'status' => 'active', 'change_reason' => 'Promotion',
    ])->assertCreated();

    expect(EmploymentRecord::where('staff_id', $this->staff->id)->count())->toBe(2);
    expect(EmploymentRecord::where('staff_id', $this->staff->id)->where('is_current', true)->count())->toBe(1);
    $this->assertDatabaseHas('staff_timelines', ['staff_id' => $this->staff->id, 'event_type' => 'hr.employment_changed']);
    $this->assertDatabaseHas('activity_logs', ['action' => 'hr.employment_changed']);
});

// ---------------- Shifts + attendance policies ----------------
it('creates a configurable shift and attendance policy', function (): void {
    $this->postJson('/api/v1/hr/shifts', [
        'school_id' => $this->school->id, 'name' => 'General', 'start_time' => '09:00', 'end_time' => '17:00',
        'grace_minutes' => 10, 'weekly_off_pattern' => [0, 6],
    ])->assertCreated()->assertJsonPath('data.grace_minutes', 10);

    $this->postJson('/api/v1/hr/attendance-policies', [
        'school_id' => $this->school->id, 'name' => 'Standard', 'grace_minutes' => 15, 'minimum_working_hours' => 8, 'overtime_eligible' => true,
    ])->assertCreated()->assertJsonPath('data.name', 'Standard');
});

// ---------------- Leave: type + policy + engine (apply/approve/reject/cancel) ----------------
it('processes a leave request through the engine and tracks balance', function (): void {
    $type = $this->postJson('/api/v1/hr/leave-types', ['school_id' => $this->school->id, 'name' => 'Casual Leave', 'code' => 'CL'])->json('data');
    $this->postJson('/api/v1/hr/leave-policies', [
        'school_id' => $this->school->id, 'leave_type_id' => $type['id'], 'name' => 'CL Policy', 'annual_allocation' => 12, 'approval_levels' => 1,
    ])->assertCreated();

    $leave = $this->postJson('/api/v1/hr/leave-requests', [
        'school_id' => $this->school->id, 'staff_id' => $this->staff->id, 'leave_type_id' => $type['id'],
        'start_date' => '2026-03-02', 'end_date' => '2026-03-03', 'reason' => 'Personal',
    ])->assertCreated()->assertJsonPath('data.status', 'pending')->assertJsonPath('data.days', '2.00')->json('data');

    $this->postJson("/api/v1/hr/leave-requests/{$leave['id']}/approve", ['notes' => 'OK'])
        ->assertOk()->assertJsonPath('data.status', 'approved');

    $balance = LeaveBalance::where('staff_id', $this->staff->id)->where('leave_type_id', $type['id'])->first();
    expect((float) $balance->used)->toBe(2.0);
    $this->assertDatabaseHas('activity_logs', ['action' => 'hr.leave_approved']);
    $this->assertDatabaseHas('communication_batches', ['event' => 'hr.leave_approved']);
});

it('supports multi-level approval before a leave is approved', function (): void {
    $type = $this->postJson('/api/v1/hr/leave-types', ['school_id' => $this->school->id, 'name' => 'Earned Leave'])->json('data');
    $this->postJson('/api/v1/hr/leave-policies', [
        'school_id' => $this->school->id, 'leave_type_id' => $type['id'], 'name' => 'EL Policy', 'annual_allocation' => 20, 'approval_levels' => 2,
    ]);

    $leave = $this->postJson('/api/v1/hr/leave-requests', [
        'school_id' => $this->school->id, 'staff_id' => $this->staff->id, 'leave_type_id' => $type['id'],
        'start_date' => '2026-04-01', 'end_date' => '2026-04-01',
    ])->json('data');

    // First approval keeps it pending (level 1 of 2).
    $this->postJson("/api/v1/hr/leave-requests/{$leave['id']}/approve")->assertOk()->assertJsonPath('data.status', 'pending');
    // Second approval finalises it.
    $this->postJson("/api/v1/hr/leave-requests/{$leave['id']}/approve")->assertOk()->assertJsonPath('data.status', 'approved');
    expect($this->getJson("/api/v1/hr/leave-requests/{$leave['id']}")->json('data.approvals'))->toHaveCount(2);
});

it('cancels an approved leave and refunds the balance', function (): void {
    $type = $this->postJson('/api/v1/hr/leave-types', ['school_id' => $this->school->id, 'name' => 'Sick Leave'])->json('data');
    $this->postJson('/api/v1/hr/leave-policies', ['school_id' => $this->school->id, 'leave_type_id' => $type['id'], 'name' => 'SL', 'annual_allocation' => 10]);

    $leave = $this->postJson('/api/v1/hr/leave-requests', [
        'school_id' => $this->school->id, 'staff_id' => $this->staff->id, 'leave_type_id' => $type['id'],
        'start_date' => '2026-05-01', 'end_date' => '2026-05-02',
    ])->json('data');
    $this->postJson("/api/v1/hr/leave-requests/{$leave['id']}/approve")->assertOk();
    $this->postJson("/api/v1/hr/leave-requests/{$leave['id']}/cancel")->assertOk()->assertJsonPath('data.status', 'cancelled');

    $balance = LeaveBalance::where('staff_id', $this->staff->id)->where('leave_type_id', $type['id'])->first();
    expect((float) $balance->used)->toBe(0.0);
});

it('rejects a leave request through the engine', function (): void {
    $type = $this->postJson('/api/v1/hr/leave-types', ['school_id' => $this->school->id, 'name' => 'Special Leave'])->json('data');
    $leave = $this->postJson('/api/v1/hr/leave-requests', [
        'school_id' => $this->school->id, 'staff_id' => $this->staff->id, 'leave_type_id' => $type['id'],
        'start_date' => '2026-06-01', 'end_date' => '2026-06-01',
    ])->json('data');

    $this->postJson("/api/v1/hr/leave-requests/{$leave['id']}/reject", ['notes' => 'Not allowed'])
        ->assertOk()->assertJsonPath('data.status', 'rejected');
    $this->assertDatabaseHas('communication_batches', ['event' => 'hr.leave_rejected']);
});

// ---------------- Holidays ----------------
it('creates a configurable holiday', function (): void {
    $this->postJson('/api/v1/hr/holidays', [
        'school_id' => $this->school->id, 'name' => 'Independence Day', 'date' => '2026-08-15', 'holiday_type' => 'national',
    ])->assertCreated()->assertJsonPath('data.holiday_type', 'national');
});

// ---------------- Performance + training + discipline ----------------
it('schedules a performance review (timeline + communication)', function (): void {
    $this->postJson('/api/v1/hr/performance', [
        'school_id' => $this->school->id, 'staff_id' => $this->staff->id, 'reviewer_id' => $this->manager->id,
        'review_period_start' => '2026-01-01', 'review_period_end' => '2026-06-30', 'status' => 'scheduled',
    ])->assertCreated();

    $this->assertDatabaseHas('staff_timelines', ['staff_id' => $this->staff->id, 'event_type' => 'hr.review_scheduled']);
    $this->assertDatabaseHas('communication_batches', ['event' => 'hr.review_scheduled']);
});

it('creates a training and enrols a participant', function (): void {
    $training = $this->postJson('/api/v1/hr/training', [
        'school_id' => $this->school->id, 'name' => 'First Aid', 'provider' => 'Red Cross', 'duration_hours' => 6,
    ])->assertCreated()->json('data');

    $this->postJson("/api/v1/hr/training/{$training['id']}/participants", ['staff_id' => $this->staff->id])
        ->assertCreated();
    $this->assertDatabaseHas('hr_training_participants', ['training_id' => $training['id'], 'staff_id' => $this->staff->id]);
    $this->assertDatabaseHas('communication_batches', ['event' => 'hr.training_assigned']);
});

it('records a disciplinary action with full history', function (): void {
    $this->postJson('/api/v1/hr/discipline', [
        'school_id' => $this->school->id, 'staff_id' => $this->staff->id, 'action_type' => 'warning',
        'subject' => 'Late arrivals', 'action_date' => '2026-02-01',
    ])->assertCreated()->assertJsonPath('data.action_type', 'warning');
    $this->assertDatabaseHas('activity_logs', ['action' => 'hr.disciplinary_recorded']);
});

// ---------------- Separation (employee never deleted) ----------------
it('separates an employee without deleting them and records a separated employment state', function (): void {
    $this->postJson('/api/v1/hr/separation', [
        'school_id' => $this->school->id, 'staff_id' => $this->staff->id, 'separation_type' => 'resignation',
        'last_working_day' => '2026-07-31', 'reason' => 'Relocation',
    ])->assertCreated()->assertJsonPath('data.separation_type', 'resignation');

    expect(Staff::whereKey($this->staff->id)->exists())->toBeTrue(); // never deleted
    expect(EmploymentRecord::where('staff_id', $this->staff->id)->where('status', 'separated')->where('is_current', true)->count())->toBe(1);
    $this->assertDatabaseHas('communication_batches', ['event' => 'hr.separation_initiated']);
});

// ---------------- Search + dashboard ----------------
it('searches leave requests and returns the HR dashboard', function (): void {
    $type = $this->postJson('/api/v1/hr/leave-types', ['school_id' => $this->school->id, 'name' => 'Casual'])->json('data');
    $this->postJson('/api/v1/hr/leave-requests', [
        'school_id' => $this->school->id, 'staff_id' => $this->staff->id, 'leave_type_id' => $type['id'],
        'start_date' => '2026-09-01', 'end_date' => '2026-09-01',
    ])->assertCreated();

    $this->getJson('/api/v1/hr/leave-requests?'.http_build_query(['search' => ['status' => 'pending']]))
        ->assertOk()->assertJsonCount(1, 'data');

    $this->getJson("/api/v1/hr/dashboard?school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['employees', 'active', 'on_leave', 'departments', 'pending_leave', 'trainings', 'performance_reviews', 'separations'],
            'charts' => ['department_distribution', 'leave_trend', 'attendance_trend', 'performance_distribution'],
        ]]);
});
