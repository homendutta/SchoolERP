<?php

declare(strict_types=1);

use App\Modules\Administration\Models\School;
use App\Modules\Attendance\Models\AttendanceRecord;
use App\Modules\Payroll\Models\Loan;
use App\Modules\Payroll\Models\Payslip;
use App\Modules\Payroll\Models\SalaryAssignment;
use App\Modules\Staff\Models\Staff;
use App\Platform\Foundation\Identity\Models\Identity;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    $this->staff = Staff::create(['school_id' => $this->school->id, 'employee_number' => 'E1', 'name' => 'Ramesh', 'status' => 'active']);

    actingAsSuperAdmin();
});

/** Create BASIC (fixed 30000) + HRA (40% of basic) structure and assign it to the employee. */
function makeStructureAndAssign(): int
{
    $school = test()->school->id;
    $basic = test()->postJson('/api/v1/payroll/components', [
        'school_id' => $school, 'name' => 'Basic', 'code' => 'BASIC', 'component_type' => 'earning',
        'calculation_type' => 'fixed', 'default_value' => 30000,
    ])->json('data');
    $hra = test()->postJson('/api/v1/payroll/components', [
        'school_id' => $school, 'name' => 'HRA', 'code' => 'HRA', 'component_type' => 'earning',
        'calculation_type' => 'percentage', 'default_value' => 40, 'based_on' => 'basic',
    ])->json('data');

    $structure = test()->postJson('/api/v1/payroll/structures', [
        'school_id' => $school, 'name' => 'Grade A', 'grade' => 'A', 'effective_date' => '2026-01-01',
        'components' => [
            ['component_id' => $basic['id'], 'sequence' => 0],
            ['component_id' => $hra['id'], 'sequence' => 1],
        ],
    ])->assertCreated()->json('data');

    test()->postJson('/api/v1/payroll/assignments', [
        'school_id' => $school, 'staff_id' => test()->staff->id, 'structure_id' => $structure['id'], 'effective_date' => '2026-01-01',
    ])->assertCreated();

    return (int) $structure['id'];
}

// ---------------- Components + structures ----------------
it('creates a salary component and a versioned structure with components', function (): void {
    $structureId = makeStructureAndAssign();

    $this->getJson("/api/v1/payroll/structures/{$structureId}")
        ->assertOk()->assertJsonPath('data.version', 1)
        ->assertJsonCount(2, 'data.components');
});

// ---------------- Salary assignment + revision (historical / immutable) ----------------
it('keeps salary history immutable when revising', function (): void {
    $structureId = makeStructureAndAssign();

    $this->postJson('/api/v1/payroll/revisions', [
        'school_id' => $this->school->id, 'staff_id' => $this->staff->id, 'structure_id' => $structureId,
        'revision_type' => 'annual_increment', 'effective_date' => '2026-04-01', 'reason' => '10% hike',
    ])->assertCreated();

    expect(SalaryAssignment::where('staff_id', $this->staff->id)->count())->toBe(2);
    expect(SalaryAssignment::where('staff_id', $this->staff->id)->where('is_current', true)->count())->toBe(1);
    expect(SalaryAssignment::where('staff_id', $this->staff->id)->where('is_current', true)->value('revision_number'))->toBe(2);
    $this->assertDatabaseHas('activity_logs', ['action' => 'payroll.salary_revised']);
    $this->assertDatabaseHas('staff_timelines', ['staff_id' => $this->staff->id, 'event_type' => 'payroll.salary_revised']);
    $this->assertDatabaseHas('communication_batches', ['event' => 'payroll.salary_revision']);
});

// ---------------- Statutory + loans + overtime + arrears ----------------
it('configures statutory components without hardcoded rates', function (): void {
    $this->postJson('/api/v1/payroll/statutory', [
        'school_id' => $this->school->id, 'name' => 'Provident Fund', 'code' => 'PF', 'statutory_type' => 'pf',
        'calculation_type' => 'percentage', 'employee_rate' => 12, 'employer_rate' => 12, 'based_on' => 'basic',
    ])->assertCreated()->assertJsonPath('data.statutory_type', 'pf');
});

it('approves a loan and notifies through the engine', function (): void {
    $loan = $this->postJson('/api/v1/payroll/loans', [
        'school_id' => $this->school->id, 'staff_id' => $this->staff->id, 'loan_type' => 'loan',
        'principal' => 12000, 'installment_amount' => 2000,
    ])->assertCreated()->json('data');
    expect((float) $loan['balance'])->toBe(12000.0);

    $this->postJson("/api/v1/payroll/loans/{$loan['id']}/approve")->assertOk()->assertJsonPath('data.status', 'active');
    $this->assertDatabaseHas('communication_batches', ['event' => 'payroll.loan_approved']);
});

// ---------------- Payroll engine: process, payslip, idempotency ----------------
it('processes payroll, generates a payslip and is idempotent', function (): void {
    makeStructureAndAssign();
    $this->postJson('/api/v1/payroll/statutory', [
        'school_id' => $this->school->id, 'name' => 'Provident Fund', 'code' => 'PF', 'statutory_type' => 'pf',
        'calculation_type' => 'percentage', 'employee_rate' => 12, 'employer_rate' => 12, 'based_on' => 'basic',
    ]);

    $run = $this->postJson('/api/v1/payroll/runs', [
        'school_id' => $this->school->id, 'period_year' => 2026, 'period_month' => 3, 'label' => 'March 2026',
    ])->assertCreated()->assertJsonPath('data.status', 'draft')->json('data');
    expect($run['run_number'])->not->toBeNull();

    $this->postJson("/api/v1/payroll/runs/{$run['id']}/process")->assertOk()->assertJsonPath('data.status', 'completed');

    // gross = basic 30000 + HRA 12000 = 42000; PF employee 3600; net = 38400; employer = 3600.
    $payslip = Payslip::where('staff_id', $this->staff->id)->first();
    expect((float) $payslip->gross_earnings)->toBe(42000.0);
    expect((float) $payslip->total_deductions)->toBe(3600.0);
    expect((float) $payslip->employer_contributions)->toBe(3600.0);
    expect((float) $payslip->net_pay)->toBe(38400.0);
    expect($payslip->payslip_number)->not->toBeNull();
    $this->assertDatabaseHas('communication_batches', ['event' => 'payroll.generated']);

    // Idempotent: processing again must not create a duplicate payslip.
    $this->postJson("/api/v1/payroll/runs/{$run['id']}/process")->assertOk();
    expect(Payslip::where('staff_id', $this->staff->id)->count())->toBe(1);
});

it('deducts an approved loan installment during processing', function (): void {
    makeStructureAndAssign();
    $loan = Loan::create([
        'school_id' => $this->school->id, 'staff_id' => $this->staff->id, 'loan_type' => 'loan',
        'principal' => 10000, 'balance' => 10000, 'installment_amount' => 2000, 'status' => 'active',
    ]);

    $run = $this->postJson('/api/v1/payroll/runs', ['school_id' => $this->school->id, 'period_year' => 2026, 'period_month' => 5])->json('data');
    $this->postJson("/api/v1/payroll/runs/{$run['id']}/process")->assertOk();

    expect((float) $loan->fresh()->balance)->toBe(8000.0);
    $payslip = Payslip::where('staff_id', $this->staff->id)->first();
    // net = 42000 - loan 2000 = 40000 (no statutory configured here).
    expect((float) $payslip->net_pay)->toBe(40000.0);
    $this->assertDatabaseHas('payroll_payslip_lines', ['payslip_id' => $payslip->id, 'code' => 'LOAN']);
});

// ---------------- Attendance integration (read-only) ----------------
it('reads attendance and applies loss of pay without editing attendance', function (): void {
    makeStructureAndAssign();
    $identityId = $this->staff->identity_id;
    foreach (range(1, 20) as $d) {
        AttendanceRecord::create([
            'school_id' => $this->school->id, 'identity_id' => $identityId, 'owner_type' => Staff::class, 'owner_id' => $this->staff->id,
            'attendance_date' => sprintf('2026-06-%02d', $d), 'status' => 'present', 'source' => 'manual',
        ]);
    }
    foreach ([21, 22] as $d) {
        AttendanceRecord::create([
            'school_id' => $this->school->id, 'identity_id' => $identityId, 'owner_type' => Staff::class, 'owner_id' => $this->staff->id,
            'attendance_date' => sprintf('2026-06-%02d', $d), 'status' => 'absent', 'source' => 'manual',
        ]);
    }

    $run = $this->postJson('/api/v1/payroll/runs', ['school_id' => $this->school->id, 'period_year' => 2026, 'period_month' => 6])->json('data');
    $this->postJson("/api/v1/payroll/runs/{$run['id']}/process")->assertOk();

    $payslip = Payslip::where('staff_id', $this->staff->id)->first();
    expect((float) $payslip->present_days)->toBe(20.0);
    expect((float) $payslip->absent_days)->toBe(2.0);
    expect((float) $payslip->lwp_days)->toBe(2.0);
    $this->assertDatabaseHas('payroll_payslip_lines', ['payslip_id' => $payslip->id, 'code' => 'LOP']);
    expect((float) $payslip->net_pay)->toBeLessThan(42000.0); // pay reduced by LOP
    $this->assertDatabaseCount('attendance_records', 22); // attendance untouched
});

// ---------------- Payslip data + QR + settlement ----------------
it('exposes structured payslip data with an identity QR and records settlement', function (): void {
    makeStructureAndAssign();
    $run = $this->postJson('/api/v1/payroll/runs', ['school_id' => $this->school->id, 'period_year' => 2026, 'period_month' => 7])->json('data');
    $this->postJson("/api/v1/payroll/runs/{$run['id']}/process")->assertOk();
    $payslip = Payslip::where('staff_id', $this->staff->id)->first();

    $identityNumber = Identity::find($this->staff->identity_id)->identity_number;
    $this->getJson("/api/v1/payroll/payslips/{$payslip->id}")
        ->assertOk()
        ->assertJsonPath('data.qr.identity_number', $identityNumber)
        ->assertJsonPath('data.qr.payslip_number', $payslip->payslip_number)
        ->assertJsonCount(2, 'data.lines');

    $this->postJson("/api/v1/payroll/payslips/{$payslip->id}/settle", ['settlement_status' => 'paid'])
        ->assertOk()->assertJsonPath('data.settlement_status', 'paid');
});

// ---------------- Locked run is immutable ----------------
it('locks a payroll run and prevents further edits', function (): void {
    makeStructureAndAssign();
    $run = $this->postJson('/api/v1/payroll/runs', ['school_id' => $this->school->id, 'period_year' => 2026, 'period_month' => 8])->json('data');
    $this->postJson("/api/v1/payroll/runs/{$run['id']}/process")->assertOk();
    $this->postJson("/api/v1/payroll/runs/{$run['id']}/lock")->assertOk()->assertJsonPath('data.status', 'locked');
    $this->assertDatabaseHas('activity_logs', ['action' => 'payroll.locked']);
    $this->assertDatabaseHas('communication_batches', ['event' => 'payroll.locked']);

    // A locked run cannot be edited or reprocessed.
    $this->putJson("/api/v1/payroll/runs/{$run['id']}", ['label' => 'Changed'])->assertStatus(422);
    $this->postJson("/api/v1/payroll/runs/{$run['id']}/process")->assertStatus(422);
});

// ---------------- Search + dashboard ----------------
it('searches payslips and returns the payroll dashboard', function (): void {
    makeStructureAndAssign();
    $run = $this->postJson('/api/v1/payroll/runs', ['school_id' => $this->school->id, 'period_year' => 2026, 'period_month' => 9])->json('data');
    $this->postJson("/api/v1/payroll/runs/{$run['id']}/process")->assertOk();

    $this->getJson('/api/v1/payroll/payslips?'.http_build_query(['filter' => ['staff_id' => $this->staff->id]]))
        ->assertOk()->assertJsonCount(1, 'data');

    $this->getJson("/api/v1/payroll/dashboard?school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['employees_processed', 'pending_payroll', 'payroll_cost', 'net_salary', 'deductions', 'employer_contributions', 'pending_loans', 'payroll_runs'],
            'charts' => ['payroll_trend', 'department_cost', 'salary_distribution', 'deduction_breakdown'],
        ]]);
});
