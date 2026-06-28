<?php

declare(strict_types=1);

use App\Modules\Administration\Models\MasterDataType;
use App\Modules\Administration\Models\School;
use App\Modules\Staff\Models\Staff;
use App\Platform\Foundation\Media\Models\Media;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    $deptType = MasterDataType::create(['slug' => 'departments', 'name' => 'Departments']);
    $this->teaching = $deptType->values()->create(['label' => 'Teaching', 'value' => 'teaching']);
    $this->admin = $deptType->values()->create(['label' => 'Administration', 'value' => 'administration']);
    $desigType = MasterDataType::create(['slug' => 'designations', 'name' => 'Designations']);
    $this->teacher = $desigType->values()->create(['label' => 'Teacher', 'value' => 'teacher']);
    actingAsSuperAdmin();
});

function makeStaff(int $schoolId, array $overrides = []): array
{
    return test()->postJson('/api/v1/staff', array_merge([
        'school_id' => $schoolId,
        'name' => 'Ramesh Kumar',
        'phone' => '9876500000',
        'email' => 'ramesh@example.test',
        'department_id' => test()->teaching->id,
        'designation_id' => test()->teacher->id,
        'employment_type' => 'full_time',
        'joining_date' => '2025-04-01',
        'is_teaching' => true,
    ], $overrides))->assertCreated()->json('data');
}

// ---------------- Create + Employee Number ----------------
it('creates staff with a generated employee number, timeline and audit', function (): void {
    $staff = makeStaff($this->school->id);

    expect($staff['employee_number'])->not->toBeNull();
    expect($staff['status'])->toBe('active');

    $this->assertDatabaseHas('staff_timelines', ['staff_id' => $staff['id'], 'event_type' => 'staff.created']);
    $this->assertDatabaseHas('activity_logs', ['action' => 'staff.created']);
});

it('generates sequential, unique employee numbers', function (): void {
    $a = makeStaff($this->school->id, ['name' => 'One']);
    $b = makeStaff($this->school->id, ['name' => 'Two']);

    expect($a['employee_number'])->not->toBe($b['employee_number']);
});

// ---------------- Search ----------------
it('searches staff by employee number, name and department', function (): void {
    $a = makeStaff($this->school->id, ['name' => 'Alpha']);
    makeStaff($this->school->id, ['name' => 'Beta', 'department_id' => $this->admin->id, 'is_teaching' => false]);

    $this->getJson('/api/v1/staff?'.http_build_query(['search' => ['name' => 'Alpha']]))
        ->assertOk()->assertJsonCount(1, 'data');

    $this->getJson('/api/v1/staff?'.http_build_query(['search' => ['employee_number' => $a['employee_number']]]))
        ->assertOk()->assertJsonCount(1, 'data');

    $this->getJson('/api/v1/staff?'.http_build_query(['filter' => ['department_id' => $this->admin->id]]))
        ->assertOk()->assertJsonCount(1, 'data');
});

// ---------------- Profile update + department-change timeline ----------------
it('updates the profile and records a department-change event', function (): void {
    $staff = makeStaff($this->school->id);

    $this->putJson("/api/v1/staff/{$staff['id']}", ['department_id' => $this->admin->id, 'is_teaching' => false])
        ->assertOk()->assertJsonPath('data.department_id', $this->admin->id);

    $this->assertDatabaseHas('staff_timelines', ['staff_id' => $staff['id'], 'event_type' => 'staff.department_changed']);
});

// ---------------- Qualifications ----------------
it('manages unlimited qualifications', function (): void {
    $staff = makeStaff($this->school->id);

    $this->postJson('/api/v1/staff-qualifications', [
        'school_id' => $this->school->id, 'staff_id' => $staff['id'],
        'qualification' => 'B.Ed', 'institution' => 'XYZ', 'year' => '2018', 'grade' => 'A',
    ])->assertCreated()->assertJsonPath('data.qualification', 'B.Ed');

    $this->getJson('/api/v1/staff-qualifications?'.http_build_query(['filter' => ['staff_id' => $staff['id']]]))
        ->assertOk()->assertJsonCount(1, 'data');
});

// ---------------- Experience ----------------
it('manages unlimited experience history', function (): void {
    $staff = makeStaff($this->school->id);

    $this->postJson('/api/v1/staff-experience', [
        'school_id' => $this->school->id, 'staff_id' => $staff['id'],
        'organization' => 'Old School', 'designation' => 'Teacher',
        'from_date' => '2015-06-01', 'to_date' => '2020-05-31', 'reason_for_leaving' => 'Better role',
    ])->assertCreated()->assertJsonPath('data.organization', 'Old School');
});

// ---------------- Documents (Media id only) ----------------
it('attaches a document by media id with a master-data type', function (): void {
    $staff = makeStaff($this->school->id);
    $type = MasterDataType::create(['slug' => 'staff_document_types', 'name' => 'Staff Document Types']);
    $docType = $type->values()->create(['label' => 'Resume', 'value' => 'resume']);
    $media = Media::create([
        'disk' => 'local', 'visibility' => 'private', 'path' => 'staff/x.pdf',
        'filename' => 'x.pdf', 'original_filename' => 'cv.pdf', 'stored_filename' => 'x.pdf',
    ]);

    $this->postJson('/api/v1/staff-documents', [
        'school_id' => $this->school->id, 'staff_id' => $staff['id'],
        'document_type_id' => $docType->id, 'media_id' => $media->id, 'title' => 'CV',
    ])->assertCreated()->assertJsonPath('data.media_id', $media->id);

    $this->assertDatabaseHas('staff_timelines', ['staff_id' => $staff['id'], 'event_type' => 'staff.document_added']);
});

// ---------------- Timeline ----------------
it('returns the staff timeline newest first', function (): void {
    $staff = makeStaff($this->school->id);

    $this->getJson("/api/v1/staff-timeline?staff_id={$staff['id']}")
        ->assertOk()->assertJsonPath('data.0.event_type', 'staff.created');
});

// ---------------- Dashboard ----------------
it('returns dashboard widgets and charts', function (): void {
    makeStaff($this->school->id);

    $this->getJson("/api/v1/staff/dashboard?school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['total_staff', 'teaching_staff', 'non_teaching_staff', 'active', 'on_leave', 'new_joinees', 'resigned'],
            'charts' => ['department_distribution', 'designation_distribution', 'monthly_joining'],
        ]]);
});

// ---------------- Import ----------------
it('validates and imports staff via the import engine', function (): void {
    makeStaff($this->school->id, ['name' => 'Existing']); // employee_number "1"

    $rows = [
        ['school_id' => $this->school->id, 'employee_number' => 'E100', 'name' => 'Import One', 'department_id' => $this->teaching->id, 'designation_id' => $this->teacher->id, 'joining_date' => '2025-04-01'],
        ['school_id' => $this->school->id, 'employee_number' => 'E100', 'name' => 'Dup In File', 'joining_date' => '2025-04-01'], // duplicate in file
        ['school_id' => $this->school->id, 'employee_number' => 'E101', 'name' => 'Bad Dept', 'department_id' => 99999, 'joining_date' => '2025-04-01'], // invalid department
        ['school_id' => $this->school->id, 'employee_number' => 'E102', 'name' => 'Bad Date', 'joining_date' => 'not-a-date'], // invalid joining date
    ];

    $this->postJson('/api/v1/staff-import/validate', ['rows' => $rows])
        ->assertOk()->assertJsonPath('data.valid', false);

    $this->postJson('/api/v1/staff-import/execute', ['rows' => $rows])
        ->assertOk()->assertJsonPath('data.created', 1)->assertJsonPath('data.skipped', 3);

    $this->assertDatabaseHas('staff', ['employee_number' => 'E100']);
});

// ---------------- Export ----------------
it('exports staff as csv', function (): void {
    makeStaff($this->school->id);
    $res = $this->get('/api/v1/staff-export?format=csv');
    $res->assertOk();
    expect($res->headers->get('content-type'))->toContain('text/csv');
});

// ---------------- Master Data references (gender, blood group) ----------------
it('links gender and blood group from master data', function (): void {
    $g = MasterDataType::create(['slug' => 'genders', 'name' => 'Genders']);
    $male = $g->values()->create(['label' => 'Male', 'value' => 'male']);
    $b = MasterDataType::create(['slug' => 'blood_groups', 'name' => 'Blood Groups']);
    $oPos = $b->values()->create(['label' => 'O+', 'value' => 'o_pos']);

    $staff = makeStaff($this->school->id, ['gender_id' => $male->id, 'blood_group_id' => $oPos->id]);

    expect($staff['gender_id'])->toBe($male->id);
    $this->getJson("/api/v1/staff/{$staff['id']}")
        ->assertOk()
        ->assertJsonPath('data.gender.label', 'Male')
        ->assertJsonPath('data.blood_group.label', 'O+');
});

// ---------------- Reporting manager (self FK) ----------------
it('sets a reporting manager as a self reference', function (): void {
    $manager = makeStaff($this->school->id, ['name' => 'Principal']);
    $staff = makeStaff($this->school->id, ['name' => 'Teacher', 'reporting_manager_id' => $manager['id']]);

    $this->getJson("/api/v1/staff/{$staff['id']}")
        ->assertOk()
        ->assertJsonPath('data.reporting_manager_id', $manager['id'])
        ->assertJsonPath('data.reporting_manager.name', 'Principal');
});

// ---------------- Employee Number integrity ----------------
it('keeps the sequence ahead when an employee number is edited so auto numbers never collide', function (): void {
    $a = makeStaff($this->school->id, ['name' => 'One']); // auto "1"

    // Admin edits the number far ahead.
    $this->putJson("/api/v1/staff/{$a['id']}", ['employee_number' => '25050'])
        ->assertOk()->assertJsonPath('data.employee_number', '25050');

    // The change is registered + audited.
    $this->assertDatabaseHas('business_number_registry', ['type' => 'employee_number', 'number' => '25050']);
    $this->assertDatabaseHas('activity_logs', ['action' => 'staff.employee_number_changed']);

    // The next auto-generated number must not collide (continues past 25050).
    $b = makeStaff($this->school->id, ['name' => 'Two']);
    expect((int) $b['employee_number'])->toBeGreaterThan(25050);
});

it('rejects a duplicate employee number on edit', function (): void {
    $a = makeStaff($this->school->id, ['name' => 'One']);
    $b = makeStaff($this->school->id, ['name' => 'Two']);

    $this->putJson("/api/v1/staff/{$b['id']}", ['employee_number' => $a['employee_number']])
        ->assertStatus(422);
});

// ---------------- Archive (never hard delete via lifecycle) ----------------
it('archives and restores a staff record', function (): void {
    $staff = makeStaff($this->school->id);

    $this->postJson("/api/v1/staff/{$staff['id']}/archive")->assertOk();
    $this->assertSoftDeleted('staff', ['id' => $staff['id']]);

    $this->postJson("/api/v1/staff/{$staff['id']}/restore")->assertOk();
    $this->assertDatabaseHas('staff', ['id' => $staff['id'], 'deleted_at' => null]);
    expect(Staff::find($staff['id']))->not->toBeNull();
});
