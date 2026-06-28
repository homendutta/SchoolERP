<?php

declare(strict_types=1);

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Administration\Models\MasterDataType;
use App\Modules\Administration\Models\Role;
use App\Modules\Administration\Models\School;
use App\Modules\Admissions\Models\AdmissionApplication;
use App\Modules\Admissions\Models\AdmissionWorkflowStep;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    $this->year = AcademicYear::create([
        'school_id' => $this->school->id, 'name' => '2025-2026', 'slug' => '2025-2026',
        'start_date' => '2025-04-01', 'end_date' => '2026-03-31', 'status' => 'active',
    ]);
    $this->class = SchoolClass::create(['school_id' => $this->school->id, 'code' => 'C1', 'name' => 'Grade 1', 'slug' => 'grade-1', 'status' => 'active']);
    $this->section = Section::create(['class_id' => $this->class->id, 'name' => 'A', 'capacity' => 40, 'status' => 'active']);

    // Roles needed by enrollment.
    Role::create(['slug' => 'student', 'name' => 'Student', 'is_system' => true]);
    Role::create(['slug' => 'parent', 'name' => 'Parent', 'is_system' => true]);

    actingAsSuperAdmin();
});

/** Create an application via the API and return its id. */
function makeApplication(int $schoolId, int $yearId, int $classId, int $sectionId, array $overrides = []): int
{
    return test()->postJson('/api/v1/admissions/applications', array_merge([
        'school_id' => $schoolId,
        'academic_year_id' => $yearId,
        'class_id' => $classId,
        'section_id' => $sectionId,
        'student_name' => 'Asha Rao',
        'guardian_name' => 'Ravi Rao',
        'guardian_phone' => '9876500000',
        'guardian_email' => 'ravi@example.test',
    ], $overrides))->assertCreated()->json('data.id');
}

/** Move an application all the way to Approved via the default (single) workflow. */
function approveApplication(int $id): void
{
    test()->postJson("/api/v1/admissions/applications/{$id}/submit")->assertOk();
    test()->postJson("/api/v1/admissions/verification/applications/{$id}", ['status' => 'verified'])->assertOk();
    test()->postJson("/api/v1/admissions/approval/applications/{$id}/start")->assertOk();
    $step = AdmissionApplication::find($id)->approvalSteps()->first();
    test()->postJson("/api/v1/admissions/approval/steps/{$step->id}/act", ['decision' => 'approved'])->assertOk();
}

// ---------------- Enquiries ----------------
it('creates an enquiry with an auto-generated number and default status', function (): void {
    $res = $this->postJson('/api/v1/admissions/enquiries', [
        'school_id' => $this->school->id,
        'student_name' => 'New Kid',
        'phone' => '9000000000',
    ])->assertCreated();

    expect($res->json('data.enquiry_number'))->not->toBeNull();
    $res->assertJsonPath('data.status', 'new');
});

// ---------------- Applications ----------------
it('creates an application with an auto number and marks a linked enquiry converted', function (): void {
    $enquiry = $this->postJson('/api/v1/admissions/enquiries', [
        'school_id' => $this->school->id, 'student_name' => 'Asha Rao',
    ])->json('data.id');

    $res = $this->postJson('/api/v1/admissions/applications', [
        'school_id' => $this->school->id, 'academic_year_id' => $this->year->id, 'class_id' => $this->class->id,
        'student_name' => 'Asha Rao', 'guardian_name' => 'Ravi Rao', 'enquiry_id' => $enquiry,
    ])->assertCreated();

    expect($res->json('data.application_number'))->not->toBeNull();
    $res->assertJsonPath('data.status', 'draft');
    $this->assertDatabaseHas('admission_enquiries', ['id' => $enquiry, 'status' => 'converted', 'converted_application_id' => $res->json('data.id')]);
});

// ---------------- Documents + Verification ----------------
it('attaches a document with a master-data type and verifies it with history', function (): void {
    $type = MasterDataType::create(['slug' => 'document_types', 'name' => 'Document Types']);
    $birthCert = $type->values()->create(['label' => 'Birth Certificate', 'value' => 'birth_certificate']);
    $id = makeApplication($this->school->id, $this->year->id, $this->class->id, $this->section->id);

    $doc = $this->postJson('/api/v1/admissions/documents', [
        'school_id' => $this->school->id, 'application_id' => $id, 'document_type_id' => $birthCert->id, 'title' => 'BC',
    ])->assertCreated()->json('data.id');

    $this->postJson("/api/v1/admissions/verification/documents/{$doc}", ['status' => 'verified'])
        ->assertOk()->assertJsonPath('data.status', 'verified');

    $this->postJson("/api/v1/admissions/verification/applications/{$id}", ['status' => 'verified', 'remarks' => 'ok'])
        ->assertOk()->assertJsonPath('data.verification_status', 'verified');

    $this->getJson("/api/v1/admissions/verification/applications/{$id}/history")
        ->assertOk()->assertJsonCount(2, 'data');
});

// ---------------- Approval workflow ----------------
it('runs a configurable multi-step approval and approves only when all steps pass', function (): void {
    AdmissionWorkflowStep::create(['school_id' => $this->school->id, 'name' => 'Counsellor', 'sort_order' => 1, 'is_active' => true]);
    AdmissionWorkflowStep::create(['school_id' => $this->school->id, 'name' => 'Principal', 'sort_order' => 2, 'is_active' => true]);

    $id = makeApplication($this->school->id, $this->year->id, $this->class->id, $this->section->id);
    $this->postJson("/api/v1/admissions/approval/applications/{$id}/start")->assertOk();

    $steps = AdmissionApplication::find($id)->approvalSteps()->orderBy('sort_order')->get();
    expect($steps)->toHaveCount(2);

    $this->postJson("/api/v1/admissions/approval/steps/{$steps[0]->id}/act", ['decision' => 'approved'])
        ->assertOk()->assertJsonPath('data.status', 'under_review');

    $this->postJson("/api/v1/admissions/approval/steps/{$steps[1]->id}/act", ['decision' => 'approved'])
        ->assertOk()->assertJsonPath('data.status', 'approved');
});

it('rejects the application when any approval step is rejected', function (): void {
    $id = makeApplication($this->school->id, $this->year->id, $this->class->id, $this->section->id);
    $this->postJson("/api/v1/admissions/approval/applications/{$id}/start")->assertOk();
    $step = AdmissionApplication::find($id)->approvalSteps()->first();

    $this->postJson("/api/v1/admissions/approval/steps/{$step->id}/act", ['decision' => 'rejected', 'remarks' => 'no'])
        ->assertOk()->assertJsonPath('data.status', 'rejected');
});

// ---------------- Enrollment (the transactional core) ----------------
it('enrolls an approved application, creating guardian, student, record, users and credentials', function (): void {
    $id = makeApplication($this->school->id, $this->year->id, $this->class->id, $this->section->id);
    approveApplication($id);

    $res = $this->postJson("/api/v1/admissions/enroll/{$id}")->assertCreated();

    $admissionNo = $res->json('data.student.admission_number');
    expect($admissionNo)->not->toBeNull();
    expect($res->json('data.credentials.student.username'))->toBe($admissionNo);
    expect($res->json('data.credentials.parent.username'))->not->toBeNull();

    $this->assertDatabaseHas('admission_applications', ['id' => $id, 'status' => 'enrolled']);
    $this->assertDatabaseCount('students', 1);
    $this->assertDatabaseCount('guardians', 1);
    $this->assertDatabaseCount('student_academic_records', 1);

    $student = Student::first();
    expect($student->guardians()->count())->toBe(1);
    expect(StudentAcademicRecord::where('student_id', $student->id)->where('is_current', true)->exists())->toBeTrue();

    // Two users created (student + parent) with the generated usernames + roles.
    $this->assertDatabaseHas('users', ['username' => $admissionNo]);
    expect($student->user->hasRole('student'))->toBeTrue();
    expect(Guardian::first()->user->hasRole('parent'))->toBeTrue();

    // Notifications recorded (guardian email + phone present).
    $this->assertDatabaseHas('notification_outbox', ['channel' => 'email', 'status' => 'sent']);
    // Activity logged.
    $this->assertDatabaseHas('activity_logs', ['action' => 'admission.enrolled']);
});

it('refuses to enroll an application that is not approved', function (): void {
    $id = makeApplication($this->school->id, $this->year->id, $this->class->id, $this->section->id);

    $this->postJson("/api/v1/admissions/enroll/{$id}")->assertStatus(422)->assertJsonPath('code', 'NOT_APPROVED');
    $this->assertDatabaseCount('students', 0);
});

it('refuses to enroll the same application twice', function (): void {
    $id = makeApplication($this->school->id, $this->year->id, $this->class->id, $this->section->id);
    approveApplication($id);
    $this->postJson("/api/v1/admissions/enroll/{$id}")->assertCreated();

    $this->postJson("/api/v1/admissions/enroll/{$id}")->assertStatus(422)->assertJsonPath('code', 'ALREADY_ENROLLED');
    $this->assertDatabaseCount('students', 1);
});

it('generates sequential, unique admission numbers across enrollments', function (): void {
    $first = makeApplication($this->school->id, $this->year->id, $this->class->id, $this->section->id, ['student_name' => 'One']);
    approveApplication($first);
    $a = $this->postJson("/api/v1/admissions/enroll/{$first}")->json('data.student.admission_number');

    $second = makeApplication($this->school->id, $this->year->id, $this->class->id, $this->section->id, ['student_name' => 'Two']);
    approveApplication($second);
    $b = $this->postJson("/api/v1/admissions/enroll/{$second}")->json('data.student.admission_number');

    expect($a)->not->toBe($b);
});

// ---------------- Import ----------------
it('imports applications through the generic import framework', function (): void {
    $rows = [
        ['school_id' => $this->school->id, 'student_name' => 'Imp One', 'guardian_name' => 'G One', 'academic_year_id' => $this->year->id, 'class_id' => $this->class->id],
        ['school_id' => $this->school->id, 'student_name' => '', 'guardian_name' => 'G Two', 'academic_year_id' => $this->year->id, 'class_id' => $this->class->id],
    ];

    $this->postJson('/api/v1/admissions/import/validate', ['rows' => $rows])
        ->assertOk()->assertJsonPath('data.valid', false);

    $this->postJson('/api/v1/admissions/import/execute', ['rows' => $rows])
        ->assertOk()->assertJsonPath('data.created', 1)->assertJsonPath('data.skipped', 1);
});

// ---------------- Dashboard ----------------
it('returns dashboard widgets and charts', function (): void {
    makeApplication($this->school->id, $this->year->id, $this->class->id, $this->section->id);

    $this->getJson("/api/v1/admissions/dashboard?school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['today_enquiries', 'pending_applications', 'approved', 'rejected', 'month_admissions', 'conversion_rate'],
            'charts' => ['monthly_admissions', 'enquiry_sources', 'status_distribution'],
        ]]);
});
