<?php

declare(strict_types=1);

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Administration\Models\MasterDataType;
use App\Modules\Administration\Models\School;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;
use App\Platform\Foundation\Media\Models\Media;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    $this->year = AcademicYear::create(['school_id' => $this->school->id, 'name' => '2025-2026', 'slug' => '2025-2026', 'start_date' => '2025-04-01', 'end_date' => '2026-03-31', 'status' => 'active']);
    $this->year2 = AcademicYear::create(['school_id' => $this->school->id, 'name' => '2026-2027', 'slug' => '2026-2027', 'start_date' => '2026-04-01', 'end_date' => '2027-03-31', 'status' => 'active']);
    $this->classA = SchoolClass::create(['school_id' => $this->school->id, 'code' => 'C1', 'name' => 'Grade 1', 'slug' => 'grade-1', 'status' => 'active']);
    $this->classB = SchoolClass::create(['school_id' => $this->school->id, 'code' => 'C2', 'name' => 'Grade 2', 'slug' => 'grade-2', 'status' => 'active']);
    $this->sectionA = Section::create(['class_id' => $this->classA->id, 'name' => 'A', 'capacity' => 40, 'status' => 'active']);
    $this->sectionB = Section::create(['class_id' => $this->classA->id, 'name' => 'B', 'capacity' => 40, 'status' => 'active']);
    actingAsSuperAdmin();
});

/** Create a student with a primary guardian and a current academic record. */
function makeStudent(array $overrides = []): Student
{
    $school = test()->school;
    $student = Student::create(array_merge([
        'school_id' => $school->id,
        'admission_number' => '1001',
        'name' => 'Asha Rao',
        'gender' => 'female',
        'status' => 'active',
        'enrolled_on' => now()->toDateString(),
    ], $overrides));

    $guardian = Guardian::create([
        'school_id' => $school->id, 'name' => 'Ravi Rao', 'phone' => '9876500000', 'status' => 'active',
    ]);
    $student->guardians()->attach($guardian->id, ['is_primary' => true, 'emergency_contact' => true, 'financial_responsible' => true]);

    StudentAcademicRecord::create([
        'school_id' => $school->id, 'student_id' => $student->id,
        'academic_year_id' => test()->year->id, 'class_id' => test()->classA->id, 'section_id' => test()->sectionA->id,
        'admission_number' => $student->admission_number, 'status' => 'active', 'is_current' => true, 'started_on' => now()->toDateString(),
    ]);

    return $student;
}

// ---------------- Search ----------------
it('searches students by admission number, guardian and class', function (): void {
    makeStudent(['admission_number' => '1001', 'name' => 'Asha Rao']);
    makeStudent(['admission_number' => '1002', 'name' => 'Bina Sen']);

    $this->getJson('/api/v1/students?'.http_build_query(['search' => ['admission_number' => '1001']]))
        ->assertOk()->assertJsonCount(1, 'data');

    $this->getJson('/api/v1/students?'.http_build_query(['search' => ['guardian' => 'Ravi']]))
        ->assertOk()->assertJsonCount(2, 'data');

    $this->getJson('/api/v1/students?'.http_build_query(['filter' => ['class_id' => $this->classA->id]]))
        ->assertOk()->assertJsonCount(2, 'data');
});

// ---------------- Profile + update ----------------
it('shows a full profile and updates it with a timeline entry', function (): void {
    $student = makeStudent();

    $this->getJson("/api/v1/students/{$student->id}")
        ->assertOk()
        ->assertJsonPath('data.admission_number', '1001')
        ->assertJsonPath('data.current_record.class.name', 'Grade 1');

    $this->putJson("/api/v1/students/{$student->id}", ['city' => 'Pune'])
        ->assertOk()->assertJsonPath('data.city', 'Pune');

    $this->assertDatabaseHas('student_timelines', ['student_id' => $student->id, 'event_type' => 'student.profile_updated']);
    $this->assertDatabaseHas('activity_logs', ['action' => 'student.profile_updated']);
});

it('does not expose a create-student endpoint', function (): void {
    $this->postJson('/api/v1/students', ['name' => 'Hacker'])->assertStatus(405);
});

// ---------------- Medical (Master Data) ----------------
it('updates medical info with a master-data blood group', function (): void {
    $student = makeStudent();
    $type = MasterDataType::create(['slug' => 'blood_groups', 'name' => 'Blood Groups']);
    $value = $type->values()->create(['label' => 'O+', 'value' => 'o_pos']);

    $this->putJson("/api/v1/student-medical/{$student->id}", [
        'blood_group_id' => $value->id, 'allergies' => 'Peanuts',
    ])->assertOk()->assertJsonPath('data.blood_group_id', $value->id);

    $this->assertDatabaseHas('student_timelines', ['student_id' => $student->id, 'event_type' => 'student.medical_updated']);
});

// ---------------- Documents (Media id only) ----------------
it('attaches a document by media id (no paths)', function (): void {
    $student = makeStudent();
    $type = MasterDataType::create(['slug' => 'document_types', 'name' => 'Document Types']);
    $docType = $type->values()->create(['label' => 'Birth Certificate', 'value' => 'birth_certificate']);
    $media = Media::create([
        'disk' => 'local', 'visibility' => 'private', 'path' => 'students/x.pdf',
        'filename' => 'x.pdf', 'original_filename' => 'bc.pdf', 'stored_filename' => 'x.pdf',
    ]);

    $this->postJson('/api/v1/student-documents', [
        'school_id' => $this->school->id, 'student_id' => $student->id,
        'document_type_id' => $docType->id, 'media_id' => $media->id, 'title' => 'BC',
    ])->assertCreated()->assertJsonPath('data.media_id', $media->id);

    $this->assertDatabaseHas('student_timelines', ['student_id' => $student->id, 'event_type' => 'student.document_added']);
});

// ---------------- Promotion (immutable history) ----------------
it('promotes a student by creating a NEW academic record and never editing history', function (): void {
    $student = makeStudent();
    $original = $student->academicRecords()->first();

    $this->postJson("/api/v1/student-promotion/{$student->id}", [
        'academic_year_id' => $this->year2->id, 'class_id' => $this->classB->id,
    ])->assertCreated();

    // Old record is NEVER touched — placement AND lifecycle fields are immutable.
    $original->refresh();
    expect($original->class_id)->toBe($this->classA->id); // history immutable
    expect($original->ended_on)->toBeNull();              // not updated at all

    // "Current" is derived as the latest record (the new one).
    $current = $student->fresh()->currentRecord()->first();
    expect($current->id)->not->toBe($original->id);
    expect($current->class_id)->toBe($this->classB->id);
    expect($current->promoted_from_record_id)->toBe($original->id);

    $this->assertDatabaseHas('students', ['id' => $student->id, 'status' => 'promoted']);
    $this->assertDatabaseHas('student_timelines', ['student_id' => $student->id, 'event_type' => 'student.promoted']);
    $this->assertDatabaseHas('activity_logs', ['action' => 'student.promoted']);
});

// ---------------- Transfers ----------------
it('records an internal transfer and preserves history', function (): void {
    $student = makeStudent();
    $original = $student->academicRecords()->first();

    $this->postJson("/api/v1/student-transfer/{$student->id}", [
        'type' => 'internal', 'to_class_id' => $this->classA->id, 'to_section_id' => $this->sectionB->id,
        'transfer_date' => now()->toDateString(), 'reason' => 'Balancing',
    ])->assertCreated();

    // Internal transfer inserts a NEW record; the old one is left untouched.
    $original->refresh();
    expect($original->ended_on)->toBeNull();
    $current = $student->fresh()->currentRecord()->first();
    expect($current->id)->not->toBe($original->id);
    expect($current->section_id)->toBe($this->sectionB->id);
    $this->assertDatabaseHas('student_transfers', ['student_id' => $student->id, 'type' => 'internal']);
});

it('records an external transfer and marks the student transferred', function (): void {
    $student = makeStudent();

    $original = $student->academicRecords()->first();

    $this->postJson("/api/v1/student-transfer/{$student->id}", [
        'type' => 'external', 'destination_school' => 'Other School',
        'transfer_date' => now()->toDateString(), 'reason' => 'Relocation',
    ])->assertCreated();

    // External transfer closes the current record and changes status.
    $this->assertDatabaseHas('students', ['id' => $student->id, 'status' => 'transferred']);
    expect($original->fresh()->ended_on)->not->toBeNull();
});

// ---------------- Withdrawal (never delete) ----------------
it('withdraws a student without deleting any data', function (): void {
    $student = makeStudent();

    $this->postJson("/api/v1/student-withdrawal/{$student->id}", [
        'withdraw_date' => now()->toDateString(), 'reason' => 'Family',
    ])->assertCreated();

    $this->assertDatabaseHas('students', ['id' => $student->id, 'status' => 'withdrawn']);
    expect(Student::find($student->id))->not->toBeNull();
    $this->assertDatabaseHas('student_withdrawals', ['student_id' => $student->id]);
});

// ---------------- Dashboard ----------------
it('returns dashboard widgets and charts', function (): void {
    makeStudent();

    $this->getJson("/api/v1/students/dashboard?school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['total_students', 'active', 'withdrawn', 'graduated', 'promoted', 'transfers', 'new_admissions'],
            'charts' => ['monthly_admissions', 'promotions', 'withdrawals', 'gender_distribution', 'class_distribution'],
        ]]);
});

// ---------------- Import (migration mode) ----------------
it('validates and imports students via the import engine', function (): void {
    makeStudent(['admission_number' => '5000']);

    $rows = [
        ['school_id' => $this->school->id, 'admission_number' => '5001', 'name' => 'New One', 'guardian_name' => 'G1', 'academic_year_id' => $this->year->id, 'class_id' => $this->classA->id],
        ['school_id' => $this->school->id, 'admission_number' => '5000', 'name' => 'Dup', 'guardian_name' => 'G2', 'academic_year_id' => $this->year->id, 'class_id' => $this->classA->id], // duplicate
        ['school_id' => $this->school->id, 'admission_number' => '5002', 'name' => 'No Guardian', 'guardian_name' => '', 'academic_year_id' => $this->year->id, 'class_id' => $this->classA->id], // missing guardian
        ['school_id' => $this->school->id, 'admission_number' => '5003', 'name' => 'Bad Year', 'guardian_name' => 'G4', 'academic_year_id' => 99999, 'class_id' => $this->classA->id], // invalid year
    ];

    $this->postJson('/api/v1/student-import/validate', ['rows' => $rows])
        ->assertOk()->assertJsonPath('data.valid', false);

    $this->postJson('/api/v1/student-import/execute', ['rows' => $rows])
        ->assertOk()->assertJsonPath('data.created', 1)->assertJsonPath('data.skipped', 3);

    $this->assertDatabaseHas('students', ['admission_number' => '5001']);
});

// ---------------- ID card / QR ----------------
it('exposes id-card and qr preparation data', function (): void {
    $student = makeStudent();

    $card = $this->getJson("/api/v1/students/{$student->id}/id-card")->assertOk();
    $card->assertJsonPath('data.admission_number', '1001')->assertJsonPath('data.class', 'Grade 1');
    expect($card->json('data.qr_data'))->toContain('ASYLINX|1001|');

    $this->getJson("/api/v1/students/{$student->id}/qr")
        ->assertOk()->assertJsonPath('data.admission_number', '1001');
});

// ---------------- Guardian relationship (pivot, Master Data, single primary) ----------------
it('manages the guardian relationship on the pivot and enforces one primary', function (): void {
    $student = makeStudent();
    $type = MasterDataType::create(['slug' => 'relationship_types', 'name' => 'Relationship Types']);
    $father = $type->values()->create(['label' => 'Father', 'value' => 'father']);
    $second = Guardian::create(['school_id' => $this->school->id, 'name' => 'Mother', 'phone' => '900', 'status' => 'active']);

    $this->postJson("/api/v1/students/{$student->id}/guardians", [
        'guardian_id' => $second->id,
        'relationship_type_id' => $father->id,
        'is_primary' => true,
        'emergency_contact' => true,
        'pickup_authorized' => true,
    ])->assertCreated();

    // Exactly one primary guardian, and it is the newly linked one.
    $primary = $student->guardians()->wherePivot('is_primary', true)->get();
    expect($primary)->toHaveCount(1);
    expect($primary->first()->id)->toBe($second->id);
    $this->assertDatabaseHas('student_guardian', [
        'student_id' => $student->id, 'guardian_id' => $second->id,
        'relationship_type_id' => $father->id, 'is_primary' => true, 'pickup_authorized' => true,
    ]);
});

// ---------------- Import guardian de-duplication (siblings) ----------------
it('reuses an existing guardian for siblings during import', function (): void {
    $rows = [
        ['school_id' => $this->school->id, 'admission_number' => '7001', 'name' => 'Sib One', 'guardian_name' => 'Shared Parent', 'guardian_parent_number' => 'P900', 'academic_year_id' => $this->year->id, 'class_id' => $this->classA->id],
        ['school_id' => $this->school->id, 'admission_number' => '7002', 'name' => 'Sib Two', 'guardian_name' => 'Shared Parent', 'guardian_parent_number' => 'P900', 'academic_year_id' => $this->year->id, 'class_id' => $this->classA->id],
    ];

    $this->postJson('/api/v1/student-import/execute', ['rows' => $rows])
        ->assertOk()->assertJsonPath('data.created', 2);

    // One guardian, linked to both siblings.
    expect(Guardian::where('parent_number', 'P900')->count())->toBe(1);
    $this->assertDatabaseCount('student_guardian', 2);
});

// ---------------- Export ----------------
it('exports students as csv', function (): void {
    makeStudent();
    $res = $this->get('/api/v1/student-export?format=csv');
    $res->assertOk();
    expect($res->headers->get('content-type'))->toContain('text/csv');
});
