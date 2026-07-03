<?php

declare(strict_types=1);

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Administration\Models\School;
use App\Modules\Documents\Jobs\GenerateDocumentJob;
use App\Modules\Documents\Models\Category;
use App\Modules\Documents\Models\CertificateType;
use App\Modules\Documents\Models\GeneratedDocument;
use App\Modules\Documents\Models\Template;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Asylinx School', 'short_name' => 'AS', 'code' => 'AS', 'is_active' => true]);
    $this->year = AcademicYear::create(['school_id' => $this->school->id, 'name' => '2025-2026', 'slug' => '2025-2026', 'start_date' => '2025-04-01', 'end_date' => '2026-03-31', 'status' => 'active']);
    $this->class = SchoolClass::create(['school_id' => $this->school->id, 'code' => 'C1', 'name' => 'Class 1', 'slug' => 'class-1']);
    $this->student = Student::create(['school_id' => $this->school->id, 'admission_number' => 'ADM-1', 'name' => 'Anita Roy', 'status' => 'active']);
    StudentAcademicRecord::create(['school_id' => $this->school->id, 'student_id' => $this->student->id, 'academic_year_id' => $this->year->id, 'class_id' => $this->class->id, 'is_current' => true, 'status' => 'active']);

    $this->category = Category::create(['school_id' => $this->school->id, 'name' => 'Student', 'code' => 'STU']);
    $this->certType = CertificateType::create(['school_id' => $this->school->id, 'category_id' => $this->category->id, 'name' => 'Bonafide Certificate', 'code' => 'BONAFIDE', 'subject_kind' => 'student']);
    $this->template = Template::create([
        'school_id' => $this->school->id, 'category_id' => $this->category->id, 'certificate_type_id' => $this->certType->id,
        'name' => 'Bonafide v1', 'code' => 'BONA', 'html' => 'This certifies {{student.name}} ({{student.admission_no}}) of {{class.name}}. Code: {{document.verification_code}}',
    ]);

    actingAsSuperAdmin();
});

// ---------------- Template versioning ----------------
it('versions a template while preserving old versions', function (): void {
    $v2 = $this->postJson("/api/v1/documents/templates/{$this->template->id}/version", ['html' => 'Updated body {{student.name}}'])
        ->assertCreated()->json('data');

    expect($v2['version'])->toBe(2);
    expect((int) $v2['parent_id'])->toBe($this->template->id);
    // Version 1 remains available.
    expect(Template::where('code', 'BONA')->count())->toBe(2);
    $this->assertDatabaseHas('activity_logs', ['action' => 'documents.template_versioned']);
});

// ---------------- Generation: variable merge + immutability + identity ----------------
it('generates an immutable document with merged variables + a verification identity', function (): void {
    $doc = $this->postJson('/api/v1/documents/generate', [
        'template_id' => $this->template->id, 'subject_kind' => 'student', 'subject_id' => $this->student->id, 'issued_to' => 'Anita Roy',
    ])->assertCreated()->json('data');

    expect($doc['document_number'])->not->toBeNull();
    expect($doc['verification_code'])->not->toBeNull();
    expect($doc['rendered_html'])->toContain('Anita Roy')->toContain('ADM-1')->toContain('Class 1');
    // Verification code merged into the body.
    expect($doc['rendered_html'])->toContain((string) $doc['verification_code']);

    $model = GeneratedDocument::find($doc['id']);
    expect($model->identity_id)->not->toBeNull();
    $this->assertDatabaseHas('activity_logs', ['action' => 'documents.generated']);
    $this->assertDatabaseHas('student_timelines', ['student_id' => $this->student->id, 'event_type' => 'documents.issued']);
    $this->assertDatabaseHas('communication_batches', ['event' => 'documents.certificate_issued']);
});

it('regenerates as a new version, preserving the previous document', function (): void {
    $first = $this->postJson('/api/v1/documents/generate', ['template_id' => $this->template->id, 'subject_kind' => 'student', 'subject_id' => $this->student->id])->json('data');

    $second = $this->postJson("/api/v1/documents/history/{$first['id']}/regenerate")->assertCreated()->json('data');

    expect($second['version'])->toBe(2);
    expect((int) $second['parent_id'])->toBe($first['id']);
    // Both versions preserved (immutable history) with distinct document numbers.
    expect(GeneratedDocument::count())->toBe(2);
    expect($second['document_number'])->not->toBe($first['document_number']);
    $this->assertDatabaseHas('activity_logs', ['action' => 'documents.regenerated']);
});

// ---------------- QR + public verification ----------------
it('verifies a document publicly by number and by code, and rejects unknown ids', function (): void {
    $doc = $this->postJson('/api/v1/documents/generate', ['template_id' => $this->template->id, 'subject_kind' => 'student', 'subject_id' => $this->student->id])->json('data');

    $this->postJson('/api/v1/public/document/verify', ['method' => 'document_number', 'identifier' => $doc['document_number']])
        ->assertOk()->assertJsonPath('data.verified', true)->assertJsonPath('data.document.holder_name', 'Anita Roy');

    $this->postJson('/api/v1/public/document/verify', ['method' => 'code', 'identifier' => $doc['verification_code']])
        ->assertOk()->assertJsonPath('data.verified', true);

    $this->postJson('/api/v1/public/document/verify', ['identifier' => 'DOES-NOT-EXIST'])
        ->assertOk()->assertJsonPath('data.verified', false)->assertJsonPath('data.result', 'invalid');

    // Verification attempts are logged.
    $this->assertDatabaseHas('document_verifications', ['document_id' => $doc['id'], 'result' => 'valid']);
});

it('renders a dynamic QR svg for a document', function (): void {
    $doc = $this->postJson('/api/v1/documents/generate', ['template_id' => $this->template->id, 'subject_kind' => 'student', 'subject_id' => $this->student->id])->json('data');

    $res = $this->get("/api/v1/documents/history/{$doc['id']}/qr");
    $res->assertOk();
    expect($res->headers->get('content-type'))->toContain('image/svg+xml');
});

// ---------------- Bulk generation (queued) ----------------
it('queues bulk generation for an entire class', function (): void {
    // A second student in the same class.
    $s2 = Student::create(['school_id' => $this->school->id, 'admission_number' => 'ADM-2', 'name' => 'Bimal Das', 'status' => 'active']);
    StudentAcademicRecord::create(['school_id' => $this->school->id, 'student_id' => $s2->id, 'academic_year_id' => $this->year->id, 'class_id' => $this->class->id, 'is_current' => true, 'status' => 'active']);

    Queue::fake();
    $this->postJson('/api/v1/documents/bulk', [
        'template_id' => $this->template->id, 'subject_kind' => 'student', 'scope' => 'class', 'target' => ['class_id' => $this->class->id],
    ])->assertStatus(202)->assertJsonPath('data.queued', 2);

    Queue::assertPushed(GenerateDocumentJob::class, 2);
    $this->assertDatabaseHas('activity_logs', ['action' => 'documents.bulk_generated']);
});

// ---------------- Search + dashboard ----------------
it('searches document history and returns the dashboard', function (): void {
    $doc = $this->postJson('/api/v1/documents/generate', ['template_id' => $this->template->id, 'subject_kind' => 'student', 'subject_id' => $this->student->id])->json('data');

    $this->getJson('/api/v1/documents/history?'.http_build_query(['search' => ['document_number' => $doc['document_number']]]))
        ->assertOk()->assertJsonCount(1, 'data');

    $this->getJson("/api/v1/documents/dashboard?school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['documents_generated', 'certificates_issued', 'revoked', 'verified_documents', 'rejected_requests', 'templates'],
            'charts' => ['documents_by_category', 'monthly_generation', 'verification_trend', 'certificate_distribution'],
        ]]);
});

// ---------------- Auth ----------------
it('requires authentication for admin document endpoints', function (): void {
    // A fresh guest (no acting user).
    app('auth')->forgetGuards();
    $this->getJson('/api/v1/documents/dashboard')->assertStatus(401);
});
