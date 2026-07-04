<?php

declare(strict_types=1);

use App\Modules\Administration\Models\School;
use App\Modules\Finance\Models\Payment;
use App\Modules\Reports\Models\ReportExport;
use App\Modules\Students\Models\Student;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Report School', 'short_name' => 'RS', 'code' => 'RS', 'is_active' => true]);
    $this->s1 = Student::create(['school_id' => $this->school->id, 'admission_number' => 'ADM-1', 'name' => 'Anita Roy', 'status' => 'active']);
    $this->s2 = Student::create(['school_id' => $this->school->id, 'admission_number' => 'ADM-2', 'name' => 'Bimal Das', 'status' => 'active']);

    Payment::create(['school_id' => $this->school->id, 'student_id' => $this->s1->id, 'receipt_number' => 'RCP-1', 'transaction_number' => 'TXN-1', 'amount' => 1500, 'paid_on' => '2026-04-01']);
    Payment::create(['school_id' => $this->school->id, 'student_id' => $this->s2->id, 'receipt_number' => 'RCP-2', 'transaction_number' => 'TXN-2', 'amount' => 2500, 'paid_on' => '2026-04-02']);

    actingAsSuperAdmin();
});

// ---------------- Catalog ----------------
it('lists the report catalog', function (): void {
    $this->getJson('/api/v1/reports/catalog')
        ->assertOk()
        ->assertJsonFragment(['key' => 'academic.student_list'])
        ->assertJsonFragment(['key' => 'finance.fee_collection']);
});

// ---------------- Reporting engine: run + filter + sort + totals ----------------
it('runs a report with pagination and totals', function (): void {
    $this->postJson('/api/v1/reports/run', ['report_key' => 'academic.student_list', 'school_id' => $this->school->id])
        ->assertOk()->assertJsonPath('data.total', 2)->assertJsonCount(2, 'data.rows');

    // Filter (substring on name).
    $this->postJson('/api/v1/reports/run', ['report_key' => 'academic.student_list', 'school_id' => $this->school->id, 'filter' => ['name' => 'Anita']])
        ->assertOk()->assertJsonPath('data.total', 1)->assertJsonPath('data.rows.0.name', 'Anita Roy');

    // Totals (fee collection sums amount = 4000).
    $this->postJson('/api/v1/reports/run', ['report_key' => 'finance.fee_collection', 'school_id' => $this->school->id])
        ->assertOk()->assertJsonPath('data.totals.amount', 4000);
});

it('sorts report rows', function (): void {
    $desc = $this->postJson('/api/v1/reports/run', ['report_key' => 'academic.student_list', 'school_id' => $this->school->id, 'sort' => '-name'])
        ->assertOk()->json('data.rows');
    expect($desc[0]['name'])->toBe('Bimal Das');
});

// ---------------- CSV + Excel export (centralized) ----------------
it('exports a report to CSV and records the export', function (): void {
    $res = $this->post('/api/v1/reports/export', ['report_key' => 'academic.student_list', 'school_id' => $this->school->id, 'format' => 'csv']);
    $res->assertOk();
    expect($res->headers->get('content-type'))->toContain('text/csv');
    expect($res->headers->get('content-disposition'))->toContain('.csv');
    expect($res->getContent())->toContain('Anita Roy')->toContain('Admission No');

    $this->assertDatabaseHas('report_exports', ['report_key' => 'academic.student_list', 'format' => 'csv', 'status' => 'completed']);
    $this->assertDatabaseHas('activity_logs', ['action' => 'reports.exported']);
});

it('exports a report to Excel (SpreadsheetML)', function (): void {
    $res = $this->post('/api/v1/reports/export', ['report_key' => 'academic.student_list', 'school_id' => $this->school->id, 'format' => 'xlsx']);
    $res->assertOk();
    expect($res->headers->get('content-type'))->toContain('application/vnd.ms-excel');
    expect($res->getContent())->toContain('<Workbook')->toContain('Anita Roy');
});

// ---------------- Queued export (queue processing) ----------------
it('queues a large export and processes it on the queue', function (): void {
    $this->postJson('/api/v1/reports/export', ['report_key' => 'academic.student_list', 'school_id' => $this->school->id, 'format' => 'csv', 'queue' => true])
        ->assertStatus(202)->assertJsonPath('data.report_key', 'academic.student_list');

    // Sync queue driver ran the job → completed with the row count.
    $export = ReportExport::query()->where('report_key', 'academic.student_list')->latest('id')->first();
    expect($export->status->value)->toBe('completed');
    expect($export->row_count)->toBe(2);
});

// ---------------- Print / PDF engine ----------------
it('renders a print-ready HTML document', function (): void {
    $res = $this->post('/api/v1/reports/print', [
        'report_key' => 'academic.student_list', 'school_id' => $this->school->id,
        'options' => ['paper_size' => 'a4', 'orientation' => 'portrait', 'header' => 'Student Roster', 'signature' => 'Principal'],
    ]);
    $res->assertOk();
    $html = $res->getContent();
    expect($html)->toContain('@page')->toContain('Anita Roy')->toContain('Principal');
    $this->assertDatabaseHas('activity_logs', ['action' => 'reports.printed']);
});

// ---------------- Saved reports ----------------
it('saves and lists a report configuration', function (): void {
    $this->postJson('/api/v1/reports/saved', [
        'school_id' => $this->school->id, 'report_key' => 'academic.student_list', 'name' => 'Active students',
        'filters' => ['status' => 'active'], 'sort' => ['name'],
    ])->assertCreated()->assertJsonPath('data.name', 'Active students');

    $this->getJson('/api/v1/reports/saved?'.http_build_query(['filter' => ['school_id' => $this->school->id]]))
        ->assertOk()->assertJsonCount(1, 'data');
});

// ---------------- Scheduled reports (queue + Communication) ----------------
it('schedules a report and runs it on demand', function (): void {
    $schedule = $this->postJson('/api/v1/reports/schedules', [
        'school_id' => $this->school->id, 'report_key' => 'finance.fee_collection', 'name' => 'Weekly collection',
        'frequency' => 'weekly', 'format' => 'csv', 'recipients' => ['bursar@school.test'],
    ])->assertCreated()->json('data');
    expect($schedule['next_run_at'])->not->toBeNull();

    $this->postJson("/api/v1/reports/schedules/{$schedule['id']}/run")->assertOk();
    // Queued export produced + delivery email published via Communication.
    $this->assertDatabaseHas('report_exports', ['report_key' => 'finance.fee_collection']);
    $this->assertDatabaseHas('communication_batches', ['event' => 'reports.scheduled_delivered']);
});

// ---------------- Dashboard ----------------
it('returns the reports dashboard', function (): void {
    $this->post('/api/v1/reports/export', ['report_key' => 'academic.student_list', 'school_id' => $this->school->id, 'format' => 'csv']);

    $this->getJson("/api/v1/reports/dashboard?school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['scheduled_reports', 'recent_exports', 'export_queue', 'failed_reports', 'total_exports'],
            'charts' => ['most_used_reports', 'export_trend', 'format_distribution'],
        ]]);
});

// ---------------- Auth ----------------
it('requires authentication', function (): void {
    app('auth')->forgetGuards();
    $this->getJson('/api/v1/reports/catalog')->assertStatus(401);
});
