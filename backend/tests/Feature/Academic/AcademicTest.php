<?php

declare(strict_types=1);

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\ClassTeacher;
use App\Modules\Administration\Models\MasterDataType;
use App\Modules\Administration\Models\School;
use App\Modules\Administration\Models\User;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    actingAsSuperAdmin();
});

/** Helper: create an academic year via the API and return its id. */
function makeYear(int $schoolId, string $name = '2025-2026'): int
{
    return test()->postJson('/api/v1/academic/academic-years', [
        'school_id' => $schoolId,
        'name' => $name,
        'start_date' => '2025-04-01',
        'end_date' => '2026-03-31',
    ])->assertCreated()->json('data.id');
}

// ---------------- Academic Years ----------------
it('creates an academic year with an auto-generated slug and uuid', function (): void {
    $res = $this->postJson('/api/v1/academic/academic-years', [
        'school_id' => $this->school->id,
        'name' => '2025-2026',
        'start_date' => '2025-04-01',
        'end_date' => '2026-03-31',
    ])->assertCreated();

    $res->assertJsonPath('data.slug', '2025-2026');
    expect($res->json('data.uuid'))->not->toBeNull();
    expect($res->json('data.version'))->toBe(1);
});

it('rejects an academic year whose end date is not after the start', function (): void {
    $this->postJson('/api/v1/academic/academic-years', [
        'school_id' => $this->school->id,
        'name' => 'Bad',
        'start_date' => '2026-03-31',
        'end_date' => '2025-04-01',
    ])->assertStatus(422);
});

it('keeps only one current academic year per school', function (): void {
    $a = makeYear($this->school->id, '2024-2025');
    $b = makeYear($this->school->id, '2025-2026');

    $this->postJson("/api/v1/academic/academic-years/{$a}/set-current")
        ->assertOk()->assertJsonPath('data.is_current', true);
    $this->postJson("/api/v1/academic/academic-years/{$b}/set-current")
        ->assertOk()->assertJsonPath('data.is_current', true);

    expect(AcademicYear::find($a)->is_current)->toBeFalse();
    expect(AcademicYear::find($b)->is_current)->toBeTrue();
});

it('enforces optimistic locking on academic year updates', function (): void {
    $id = makeYear($this->school->id);

    // Correct version succeeds and bumps the version.
    $this->putJson("/api/v1/academic/academic-years/{$id}", ['name' => 'Renamed', 'version' => 1])
        ->assertOk()->assertJsonPath('data.version', 2);

    // Stale version is rejected with a conflict.
    $this->putJson("/api/v1/academic/academic-years/{$id}", ['name' => 'Again', 'version' => 1])
        ->assertStatus(409)->assertJsonPath('code', 'STALE_VERSION');
});

it('archives and restores an academic year', function (): void {
    $id = makeYear($this->school->id);

    $this->postJson("/api/v1/academic/academic-years/{$id}/archive")->assertOk();
    $this->assertSoftDeleted('academic_years', ['id' => $id]);

    $this->postJson("/api/v1/academic/academic-years/{$id}/restore")->assertOk();
    $this->assertDatabaseHas('academic_years', ['id' => $id, 'deleted_at' => null]);
});

// ---------------- Terms ----------------
it('creates terms under an academic year', function (): void {
    $year = makeYear($this->school->id);

    $this->postJson('/api/v1/academic/terms', [
        'academic_year_id' => $year,
        'name' => 'Term 1',
        'start_date' => '2025-04-01',
        'end_date' => '2025-09-30',
    ])->assertCreated()->assertJsonPath('data.name', 'Term 1');
});

// ---------------- Classes & Sections ----------------
it('performs CRUD on classes', function (): void {
    $id = $this->postJson('/api/v1/academic/classes', [
        'school_id' => $this->school->id, 'code' => 'C1', 'name' => 'Grade 1',
    ])->assertCreated()->json('data.id');

    $this->putJson("/api/v1/academic/classes/{$id}", ['name' => 'Grade One'])
        ->assertOk()->assertJsonPath('data.name', 'Grade One');

    $this->postJson("/api/v1/academic/classes/{$id}/archive")->assertOk();
    $this->assertSoftDeleted('classes', ['id' => $id]);
});

it('validates section capacity', function (): void {
    $class = $this->postJson('/api/v1/academic/classes', [
        'school_id' => $this->school->id, 'code' => 'C2', 'name' => 'Grade 2',
    ])->assertCreated()->json('data.id');

    $this->postJson('/api/v1/academic/sections', [
        'class_id' => $class, 'name' => 'A', 'capacity' => 0,
    ])->assertStatus(422);

    $this->postJson('/api/v1/academic/sections', [
        'class_id' => $class, 'name' => 'A', 'capacity' => 40,
    ])->assertCreated()->assertJsonPath('data.capacity', 40);
});

// ---------------- Rooms & Subjects (Master Data references) ----------------
it('creates a room referencing a master-data room type', function (): void {
    $type = MasterDataType::create(['slug' => 'room_types', 'name' => 'Room Types']);
    $value = $type->values()->create(['label' => 'Lab', 'value' => 'lab']);

    $this->postJson('/api/v1/academic/rooms', [
        'school_id' => $this->school->id, 'room_type_id' => $value->id, 'code' => 'R1', 'name' => 'Physics Lab',
    ])->assertCreated()->assertJsonPath('data.room_type_id', $value->id);
});

it('creates a subject referencing a master-data subject type', function (): void {
    $type = MasterDataType::create(['slug' => 'subject_types', 'name' => 'Subject Types']);
    $value = $type->values()->create(['label' => 'Core', 'value' => 'core']);

    $this->postJson('/api/v1/academic/subjects', [
        'school_id' => $this->school->id, 'subject_type_id' => $value->id,
        'code' => 'MATH', 'name' => 'Mathematics', 'credits' => 4,
    ])->assertCreated()
        ->assertJsonPath('data.subject_type_id', $value->id)
        ->assertJsonPath('data.slug', 'mathematics');
});

// ---------------- Subject Groups (many-to-many) ----------------
it('syncs subjects on a subject group', function (): void {
    $s1 = $this->postJson('/api/v1/academic/subjects', [
        'school_id' => $this->school->id, 'code' => 'PHY', 'name' => 'Physics',
    ])->json('data.id');
    $s2 = $this->postJson('/api/v1/academic/subjects', [
        'school_id' => $this->school->id, 'code' => 'CHE', 'name' => 'Chemistry',
    ])->json('data.id');

    $group = $this->postJson('/api/v1/academic/subject-groups', [
        'school_id' => $this->school->id, 'code' => 'SCI', 'name' => 'Science',
        'subject_ids' => [$s1, $s2],
    ])->assertCreated();

    expect($group->json('data.subjects'))->toHaveCount(2);
    $this->assertDatabaseCount('subject_group_subjects', 2);
});

// ---------------- Teacher Subject Assignment ----------------
it('assigns a teacher to a subject', function (): void {
    $year = makeYear($this->school->id);
    $class = $this->postJson('/api/v1/academic/classes', [
        'school_id' => $this->school->id, 'code' => 'C3', 'name' => 'Grade 3',
    ])->json('data.id');
    $section = $this->postJson('/api/v1/academic/sections', [
        'class_id' => $class, 'name' => 'A', 'capacity' => 30,
    ])->json('data.id');
    $subject = $this->postJson('/api/v1/academic/subjects', [
        'school_id' => $this->school->id, 'code' => 'ENG', 'name' => 'English',
    ])->json('data.id');
    $teacher = User::create([
        'name' => 'Teacher One', 'email' => 't1@asylinx.test', 'username' => 't1',
        'password' => 'Password@123', 'status' => 'active', 'school_id' => $this->school->id,
    ]);

    $this->postJson('/api/v1/academic/teacher-subject-assignments', [
        'academic_year_id' => $year, 'class_id' => $class, 'section_id' => $section,
        'subject_id' => $subject, 'teacher_id' => $teacher->id, 'is_primary' => true,
    ])->assertCreated()->assertJsonPath('data.teacher_id', $teacher->id);
});

// ---------------- Class Teacher (single active + history) ----------------
it('keeps a single active class teacher and preserves history', function (): void {
    $year = makeYear($this->school->id);
    $class = $this->postJson('/api/v1/academic/classes', [
        'school_id' => $this->school->id, 'code' => 'C4', 'name' => 'Grade 4',
    ])->json('data.id');
    $section = $this->postJson('/api/v1/academic/sections', [
        'class_id' => $class, 'name' => 'A', 'capacity' => 30,
    ])->json('data.id');

    $t1 = User::create(['name' => 'CT One', 'email' => 'ct1@asylinx.test', 'username' => 'ct1', 'password' => 'Password@123', 'status' => 'active', 'school_id' => $this->school->id]);
    $t2 = User::create(['name' => 'CT Two', 'email' => 'ct2@asylinx.test', 'username' => 'ct2', 'password' => 'Password@123', 'status' => 'active', 'school_id' => $this->school->id]);

    $payload = ['academic_year_id' => $year, 'class_id' => $class, 'section_id' => $section];

    $this->postJson('/api/v1/academic/class-teachers', [...$payload, 'teacher_id' => $t1->id])->assertCreated();
    $this->postJson('/api/v1/academic/class-teachers', [...$payload, 'teacher_id' => $t2->id])->assertCreated();

    // Only one active, and it is the latest teacher.
    $active = ClassTeacher::where($payload)->where('is_active', true)->get();
    expect($active)->toHaveCount(1);
    expect($active->first()->teacher_id)->toBe($t2->id);

    // History keeps both rows.
    $this->getJson('/api/v1/academic/class-teachers/history?'.http_build_query($payload))
        ->assertOk()->assertJsonCount(2, 'data');
});
