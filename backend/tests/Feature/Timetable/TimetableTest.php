<?php

declare(strict_types=1);

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\Room;
use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Academic\Models\Subject;
use App\Modules\Administration\Models\School;
use App\Modules\Staff\Models\Staff;
use App\Modules\Timetable\Models\ClassTimetable;
use App\Modules\Timetable\Models\TimetablePeriod;
use App\Modules\Timetable\Models\TimetableSubstitution;
use App\Modules\Timetable\Services\TimetableScheduleService;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    $this->year = AcademicYear::create(['school_id' => $this->school->id, 'name' => '2025-2026', 'slug' => '2025-2026', 'start_date' => '2025-04-01', 'end_date' => '2026-03-31', 'status' => 'active']);
    $this->class = SchoolClass::create(['school_id' => $this->school->id, 'code' => 'C1', 'name' => 'Grade 1', 'slug' => 'grade-1', 'status' => 'active']);
    $this->section = Section::create(['class_id' => $this->class->id, 'name' => 'A', 'capacity' => 40, 'status' => 'active']);
    $this->math = Subject::create(['school_id' => $this->school->id, 'code' => 'MATH', 'name' => 'Mathematics', 'slug' => 'math', 'status' => 'active']);
    $this->science = Subject::create(['school_id' => $this->school->id, 'code' => 'SCI', 'name' => 'Science', 'slug' => 'science', 'status' => 'active']);
    $this->room = Room::create(['school_id' => $this->school->id, 'code' => 'R1', 'name' => 'Room 1', 'status' => 'active']);
    $this->p1 = TimetablePeriod::create(['school_id' => $this->school->id, 'name' => 'Period 1', 'code' => 'P1', 'sort_order' => 1]);
    $this->p2 = TimetablePeriod::create(['school_id' => $this->school->id, 'name' => 'Period 2', 'code' => 'P2', 'sort_order' => 2]);
    $this->teacher = Staff::create(['school_id' => $this->school->id, 'employee_number' => 'T1', 'name' => 'Mr Rao', 'status' => 'active', 'is_teaching' => true]);
    $this->teacher2 = Staff::create(['school_id' => $this->school->id, 'employee_number' => 'T2', 'name' => 'Ms Iyer', 'status' => 'active', 'is_teaching' => true]);

    actingAsSuperAdmin();
});

function slotPayload(array $overrides = []): array
{
    return array_merge([
        'school_id' => test()->school->id,
        'academic_year_id' => test()->year->id,
        'class_id' => test()->class->id,
        'section_id' => test()->section->id,
        'weekday' => 'monday',
        'period_id' => test()->p1->id,
        'subject_id' => test()->math->id,
        'teacher_id' => test()->teacher->id,
        'room_id' => test()->room->id,
    ], $overrides);
}

// ---------------- Periods ----------------
it('manages configurable periods (not hardcoded)', function (): void {
    $id = $this->postJson('/api/v1/timetable/periods', [
        'school_id' => $this->school->id, 'name' => 'Assembly', 'code' => 'ASM', 'is_break' => true, 'sort_order' => 0,
    ])->assertCreated()->json('data.id');

    $this->getJson("/api/v1/timetable/periods?filter[school_id]={$this->school->id}")->assertOk();
    $this->putJson("/api/v1/timetable/periods/{$id}", ['name' => 'Morning Assembly'])
        ->assertOk()->assertJsonPath('data.name', 'Morning Assembly');
});

// ---------------- Working days ----------------
it('configures working days per school (never hardcoded Mon-Fri)', function (): void {
    $this->postJson('/api/v1/timetable/working-days/sync', [
        'school_id' => $this->school->id,
        'days' => [
            ['weekday' => 'monday', 'is_working' => true],
            ['weekday' => 'saturday', 'is_working' => true],
            ['weekday' => 'sunday', 'is_working' => false],
        ],
    ])->assertOk();

    $this->getJson("/api/v1/timetable/working-days?filter[school_id]={$this->school->id}")
        ->assertOk()->assertJsonCount(3, 'data');
});

// ---------------- Master timetable + clash detection ----------------
it('creates a class timetable slot and writes the audit log', function (): void {
    $this->postJson('/api/v1/timetable/classes', slotPayload())
        ->assertCreated()->assertJsonPath('data.subject', 'Mathematics');

    $this->assertDatabaseHas('activity_logs', ['action' => 'timetable.created']);
    expect(ClassTimetable::count())->toBe(1);
});

it('prevents a teacher clash in the same period', function (): void {
    $this->postJson('/api/v1/timetable/classes', slotPayload())->assertCreated();

    // Same teacher, same weekday+period, different class context → TEACHER_CLASH
    $this->postJson('/api/v1/timetable/classes', slotPayload([
        'section_id' => null, 'subject_id' => $this->science->id, 'room_id' => null,
    ]))->assertStatus(422)->assertJsonPath('code', 'TEACHER_CLASH');
});

it('prevents a room clash in the same period', function (): void {
    $this->postJson('/api/v1/timetable/classes', slotPayload(['teacher_id' => $this->teacher->id]))->assertCreated();

    $this->postJson('/api/v1/timetable/classes', slotPayload([
        'section_id' => null, 'teacher_id' => $this->teacher2->id, 'subject_id' => $this->science->id,
    ]))->assertStatus(422)->assertJsonPath('code', 'ROOM_CLASH');
});

it('prevents a class having two subjects in the same period', function (): void {
    $this->postJson('/api/v1/timetable/classes', slotPayload())->assertCreated();

    $this->postJson('/api/v1/timetable/classes', slotPayload([
        'subject_id' => $this->science->id, 'teacher_id' => $this->teacher2->id, 'room_id' => null,
    ]))->assertStatus(422)->assertJsonPath('code', 'CLASS_CLASH');
});

it('allows the same teacher in a different period', function (): void {
    $this->postJson('/api/v1/timetable/classes', slotPayload())->assertCreated();
    $this->postJson('/api/v1/timetable/classes', slotPayload([
        'period_id' => $this->p2->id, 'subject_id' => $this->science->id,
    ]))->assertCreated();

    expect(ClassTimetable::count())->toBe(2);
});

// ---------------- Search ----------------
it('searches the class timetable by teacher and weekday', function (): void {
    $this->postJson('/api/v1/timetable/classes', slotPayload())->assertCreated();

    $this->getJson('/api/v1/timetable/classes?'.http_build_query(['search' => ['weekday' => 'monday']]))
        ->assertOk()->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/timetable/classes?'.http_build_query(['search' => ['teacher' => 'Rao']]))
        ->assertOk()->assertJsonCount(1, 'data');
});

// ---------------- Derived teacher + room timetables + workload ----------------
it('derives the teacher timetable and calculates workload', function (): void {
    $this->postJson('/api/v1/timetable/classes', slotPayload())->assertCreated();
    $this->postJson('/api/v1/timetable/classes', slotPayload([
        'period_id' => $this->p2->id, 'subject_id' => $this->science->id,
    ]))->assertCreated();

    $this->getJson("/api/v1/timetable/teachers/{$this->teacher->id}?academic_year_id={$this->year->id}")
        ->assertOk()
        ->assertJsonPath('data.workload.periods_per_week', 2)
        ->assertJsonCount(2, 'data.slots');
});

it('derives the room timetable', function (): void {
    $this->postJson('/api/v1/timetable/classes', slotPayload())->assertCreated();

    $this->getJson("/api/v1/timetable/rooms/{$this->room->id}?academic_year_id={$this->year->id}")
        ->assertOk()->assertJsonCount(1, 'data');
});

// ---------------- Templates + copy ----------------
it('copies a timetable into another academic year', function (): void {
    $this->postJson('/api/v1/timetable/classes', slotPayload())->assertCreated();
    $nextYear = AcademicYear::create(['school_id' => $this->school->id, 'name' => '2026-2027', 'slug' => '2026-2027', 'start_date' => '2026-04-01', 'end_date' => '2027-03-31', 'status' => 'active']);

    $this->postJson('/api/v1/timetable/templates/copy', [
        'school_id' => $this->school->id,
        'from_academic_year_id' => $this->year->id,
        'to_academic_year_id' => $nextYear->id,
    ])->assertOk()->assertJsonPath('data.copied', 1);

    expect(ClassTimetable::where('academic_year_id', $nextYear->id)->count())->toBe(1);
    $this->assertDatabaseHas('activity_logs', ['action' => 'timetable.copied']);
});

// ---------------- Substitutions ----------------
it('records a substitution without modifying the master timetable', function (): void {
    $slotId = $this->postJson('/api/v1/timetable/classes', slotPayload())->json('data.id');

    $this->postJson('/api/v1/timetable/substitutions', [
        'school_id' => $this->school->id,
        'class_timetable_id' => $slotId,
        'original_teacher_id' => $this->teacher->id,
        'substitute_teacher_id' => $this->teacher2->id,
        'date' => '2026-06-08',
        'period_id' => $this->p1->id,
        'class_id' => $this->class->id,
        'reason' => 'Teacher on leave',
    ])->assertCreated()->assertJsonPath('data.substitute_teacher', 'Ms Iyer');

    // master is untouched
    expect(ClassTimetable::find($slotId)->teacher_id)->toBe($this->teacher->id);
    expect(TimetableSubstitution::count())->toBe(1);
    $this->assertDatabaseHas('activity_logs', ['action' => 'timetable.substitution_created']);
    $this->assertDatabaseHas('staff_timelines', ['staff_id' => $this->teacher2->id, 'event_type' => 'timetable.substitution_in']);
});

// ---------------- Special events ----------------
it('stores a special event override separately', function (): void {
    $this->postJson('/api/v1/timetable/special-events', [
        'school_id' => $this->school->id, 'name' => 'Sports Day', 'event_type' => 'event',
        'start_date' => '2026-06-10', 'scope' => 'school', 'cancels_classes' => true,
    ])->assertCreated()->assertJsonPath('data.name', 'Sports Day');

    $this->getJson("/api/v1/timetable/special-events?filter[school_id]={$this->school->id}")
        ->assertOk()->assertJsonCount(1, 'data');
});

// ---------------- Dashboard ----------------
it('returns the timetable dashboard widgets and charts', function (): void {
    $this->postJson('/api/v1/timetable/classes', slotPayload())->assertCreated();

    $this->getJson("/api/v1/timetable/dashboard?school_id={$this->school->id}&academic_year_id={$this->year->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['total_timetables', 'teacher_load', 'room_usage', 'daily_classes'],
            'charts' => ['teacher_workload', 'room_utilization', 'subject_distribution', 'daily_classes'],
        ]]);
});

// ---------------- Attendance integration (Phase 17) ----------------
it('exposes the schedule for a class on a date (reusable by attendance)', function (): void {
    // 2026-06-08 is a Monday
    $this->postJson('/api/v1/timetable/classes', slotPayload())->assertCreated();

    $schedule = app(TimetableScheduleService::class)->forClassOnDate(
        $this->school->id, $this->class->id, $this->section->id, $this->year->id, '2026-06-08',
    );

    expect($schedule['weekday'])->toBe('monday');
    expect($schedule['cancelled'])->toBeFalse();
    expect($schedule['slots'])->toHaveCount(1);
    expect($schedule['slots'][0]['subject'])->toBe('Mathematics');
    expect($schedule['slots'][0]['teacher_id'])->toBe($this->teacher->id);
});

it('applies a same-day substitution and a cancelling special event to the schedule', function (): void {
    $slotId = $this->postJson('/api/v1/timetable/classes', slotPayload())->json('data.id');
    TimetableSubstitution::create([
        'school_id' => $this->school->id, 'class_timetable_id' => $slotId,
        'original_teacher_id' => $this->teacher->id, 'substitute_teacher_id' => $this->teacher2->id,
        'date' => '2026-06-08', 'period_id' => $this->p1->id, 'class_id' => $this->class->id, 'status' => 'planned',
    ]);

    $schedule = app(TimetableScheduleService::class)->forClassOnDate(
        $this->school->id, $this->class->id, $this->section->id, $this->year->id, '2026-06-08',
    );

    expect($schedule['slots'][0]['is_substituted'])->toBeTrue();
    expect($schedule['slots'][0]['teacher_id'])->toBe($this->teacher2->id);
});
