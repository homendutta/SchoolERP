<?php

declare(strict_types=1);

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Administration\Models\School;
use App\Modules\Finance\Models\FeeMaster;
use App\Modules\Finance\Models\Payment;
use App\Modules\Finance\Models\StudentFee;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);
    $this->year = AcademicYear::create(['school_id' => $this->school->id, 'name' => '2025-2026', 'slug' => '2025-2026', 'start_date' => '2025-04-01', 'end_date' => '2026-03-31', 'status' => 'active']);
    $this->class = SchoolClass::create(['school_id' => $this->school->id, 'code' => 'C8', 'name' => 'Class VIII', 'slug' => 'class-8', 'status' => 'active']);
    $this->section = Section::create(['class_id' => $this->class->id, 'name' => 'A', 'capacity' => 40, 'status' => 'active']);

    $this->makeStudent = function (string $adm, string $name, string $enrolled): Student {
        $student = Student::create(['school_id' => $this->school->id, 'admission_number' => $adm, 'name' => $name, 'status' => 'active', 'enrolled_on' => $enrolled]);
        StudentAcademicRecord::create([
            'school_id' => $this->school->id, 'student_id' => $student->id, 'academic_year_id' => $this->year->id,
            'class_id' => $this->class->id, 'section_id' => $this->section->id, 'status' => 'active', 'is_current' => true, 'started_on' => $enrolled,
        ]);

        return $student->refresh();
    };

    $this->s1 = ($this->makeStudent)('1001', 'Asha', '2024-04-01');
    $this->s2 = ($this->makeStudent)('1002', 'Bina', '2025-04-01');

    actingAsSuperAdmin();

    // Configurable fee category.
    $this->categoryId = $this->postJson('/api/v1/finance/categories', ['school_id' => $this->school->id, 'name' => 'Tuition', 'code' => 'TUI'])->json('data.id');

    // Two fee masters (Tuition overdue, Transport future).
    $this->tuition = FeeMaster::create(['school_id' => $this->school->id, 'fee_category_id' => $this->categoryId, 'academic_year_id' => $this->year->id, 'name' => 'Tuition Fee', 'amount' => 1000, 'due_date' => '2026-01-01', 'frequency' => 'yearly']);
    $this->transport = FeeMaster::create(['school_id' => $this->school->id, 'fee_category_id' => $this->categoryId, 'academic_year_id' => $this->year->id, 'name' => 'Transport Fee', 'amount' => 500, 'due_date' => '2026-12-01', 'frequency' => 'yearly']);

    // Fee structure combining both.
    $this->structureId = $this->postJson('/api/v1/finance/structures', [
        'school_id' => $this->school->id, 'academic_year_id' => $this->year->id, 'class_id' => $this->class->id, 'name' => 'Class VIII Fees',
        'items' => [['fee_master_id' => $this->tuition->id], ['fee_master_id' => $this->transport->id]],
    ])->assertCreated()->json('data.id');
});

function assignFee(int $studentId): int
{
    return test()->postJson('/api/v1/finance/student-fees/assign', [
        'structure_id' => test()->structureId, 'student_id' => $studentId,
    ])->assertCreated()->json('data.id');
}

// ---------------- Fee definition ----------------
it('configures categories, masters and structures (combining masters)', function (): void {
    $this->getJson("/api/v1/finance/categories?filter[school_id]={$this->school->id}")->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("/api/v1/finance/structures/{$this->structureId}")
        ->assertOk()->assertJsonPath('data.items_count', 2);
});

// ---------------- Assignment ----------------
it('assigns a fee structure to a student with denormalized line items', function (): void {
    $feeId = assignFee($this->s1->id);
    $this->getJson("/api/v1/finance/student-fees/{$feeId}")
        ->assertOk()
        ->assertJsonPath('data.total_amount', 1500)
        ->assertJsonPath('data.net_amount', 1500)
        ->assertJsonCount(2, 'data.items');

    $this->assertDatabaseHas('activity_logs', ['action' => 'finance.fee_assigned']);
    $this->assertDatabaseHas('student_timelines', ['student_id' => $this->s1->id, 'event_type' => 'finance.fee_assigned']);
});

it('bulk-assigns a fee structure to a class', function (): void {
    $this->postJson('/api/v1/finance/student-fees/assign', [
        'structure_id' => $this->structureId, 'bulk' => true, 'class_id' => $this->class->id,
    ])->assertOk()->assertJsonPath('data.assigned', 2);

    expect(StudentFee::count())->toBe(2);
});

// ---------------- Discounts & scholarships ----------------
it('applies a configurable discount and reduces the net (masters untouched)', function (): void {
    $feeId = assignFee($this->s1->id);
    $discountId = $this->postJson('/api/v1/finance/discounts', ['school_id' => $this->school->id, 'name' => 'Merit', 'method' => 'percentage', 'value' => 10])->json('data.id');

    $this->postJson("/api/v1/finance/student-fees/{$feeId}/discount", ['discount_id' => $discountId])
        ->assertOk()->assertJsonPath('data.discount_amount', 150)->assertJsonPath('data.net_amount', 1350);

    // Fee master is never modified.
    expect(FeeMaster::find($this->tuition->id)->amount)->toBe(1000.0);
});

it('awards a full scholarship independently of discounts', function (): void {
    $feeId = assignFee($this->s1->id);
    $schId = $this->postJson('/api/v1/finance/scholarships', ['school_id' => $this->school->id, 'name' => 'Full Merit', 'type' => 'full'])->json('data.id');

    $this->postJson("/api/v1/finance/student-fees/{$feeId}/scholarship", ['scholarship_id' => $schId])
        ->assertOk()->assertJsonPath('data.scholarship_amount', 1500)->assertJsonPath('data.net_amount', 0);

    $this->assertDatabaseHas('student_scholarships', ['student_id' => $this->s1->id]);
});

// ---------------- Sibling discounts ----------------
it('applies a configurable sibling concession by child order', function (): void {
    $guardian = Guardian::create(['school_id' => $this->school->id, 'name' => 'Parent', 'relation' => 'father']);
    $this->s1->guardians()->attach($guardian->id, ['is_primary' => true]);
    $this->s2->guardians()->attach($guardian->id, ['is_primary' => true]);

    $this->postJson('/api/v1/finance/sibling-discounts', ['school_id' => $this->school->id, 'name' => '2nd Child', 'child_order' => 2, 'method' => 'percentage', 'value' => 20])->assertCreated();

    $feeId = assignFee($this->s2->id); // Bina is the 2nd child
    $this->postJson("/api/v1/finance/student-fees/{$feeId}/sibling-discount")
        ->assertOk()->assertJsonPath('data.discount_amount', 300)->assertJsonPath('data.net_amount', 1200);
});

// ---------------- Payments + allocation + ledger ----------------
it('records a payment with number-generator receipts, FIFO allocation and a ledger entry', function (): void {
    assignFee($this->s1->id);

    $payment = $this->postJson('/api/v1/finance/payments', [
        'school_id' => $this->school->id, 'student_id' => $this->s1->id, 'amount' => 1000,
    ])->assertCreated()->json('data');

    expect($payment['receipt_number'])->not->toBeNull();
    expect($payment['transaction_number'])->not->toBeNull();
    expect($payment['allocations'])->toHaveCount(1); // FIFO → Tuition (earliest due)
    expect((float) $payment['allocations'][0]['amount'])->toBe(1000.0);

    // Student fee becomes partially paid.
    $fee = StudentFee::where('student_id', $this->s1->id)->first();
    expect($fee->paid_amount)->toBe(1000.0)->and($fee->status->value)->toBe('partial');

    // Ledger entry (credit) generated automatically and independent of payment.
    $this->assertDatabaseHas('ledger_entries', ['entry_type' => 'credit', 'amount' => 1000]);
    $this->assertDatabaseHas('student_timelines', ['student_id' => $this->s1->id, 'event_type' => 'finance.payment_received']);
});

it('supports partial payments until fully paid', function (): void {
    assignFee($this->s1->id);
    $this->postJson('/api/v1/finance/payments', ['school_id' => $this->school->id, 'student_id' => $this->s1->id, 'amount' => 1000])->assertCreated();
    $this->postJson('/api/v1/finance/payments', ['school_id' => $this->school->id, 'student_id' => $this->s1->id, 'amount' => 500])->assertCreated();

    $fee = StudentFee::where('student_id', $this->s1->id)->first();
    expect($fee->paid_amount)->toBe(1500.0)->and($fee->status->value)->toBe('paid');
    expect(Payment::where('student_id', $this->s1->id)->count())->toBe(2); // each its own transaction
});

// ---------------- Refunds ----------------
it('refunds without deleting the payment and writes a debit ledger entry', function (): void {
    assignFee($this->s1->id);
    $paymentId = $this->postJson('/api/v1/finance/payments', ['school_id' => $this->school->id, 'student_id' => $this->s1->id, 'amount' => 1000])->json('data.id');

    $this->postJson('/api/v1/finance/refunds', ['payment_id' => $paymentId, 'amount' => 200, 'reason' => 'Overpaid'])
        ->assertCreated()->assertJsonPath('data.amount', 200);

    expect(Payment::find($paymentId))->not->toBeNull(); // never deleted
    expect(Payment::find($paymentId)->refunded_amount)->toBe(200.0);
    $this->assertDatabaseHas('ledger_entries', ['entry_type' => 'debit', 'amount' => 200]);
});

// ---------------- Adjustments ----------------
it('creates an independent waiver adjustment that reduces due', function (): void {
    assignFee($this->s1->id);

    $this->postJson('/api/v1/finance/adjustments', [
        'school_id' => $this->school->id, 'student_id' => $this->s1->id, 'type' => 'waiver', 'amount' => 300, 'reason' => 'Hardship',
    ])->assertCreated()->assertJsonPath('data.type', 'waiver');

    $this->assertDatabaseHas('ledger_entries', ['entry_type' => 'credit', 'amount' => 300]);

    // Due tracking reflects the waiver.
    $due = $this->getJson("/api/v1/finance/due-tracking?student_id={$this->s1->id}")->json('data');
    expect((float) $due['adjustments'])->toBe(300.0);
});

// ---------------- Due tracking + fines ----------------
it('calculates due, overdue and fine live (never snapshotted)', function (): void {
    assignFee($this->s1->id);
    // Configurable flat fine on overdue Tuition (due 2026-01-01, today is later).
    $this->postJson('/api/v1/finance/fines', ['school_id' => $this->school->id, 'name' => 'Late Fee', 'fee_category_id' => $this->categoryId, 'mode' => 'flat', 'amount' => 50, 'grace_period_days' => 0])->assertCreated();

    $due = $this->getJson("/api/v1/finance/due-tracking?student_id={$this->s1->id}&as_of=2026-06-28")->json('data');
    expect((float) $due['net_amount'])->toBe(1500.0);
    expect((float) $due['overdue'])->toBe(1000.0); // Tuition overdue
    expect((float) $due['fine'])->toBe(50.0);
    expect((float) $due['outstanding'])->toBe(1550.0); // 1500 + 50 fine
});

// ---------------- Defaulters ----------------
it('generates a dynamic defaulter list', function (): void {
    assignFee($this->s1->id);

    $defaulters = $this->getJson("/api/v1/finance/defaulters?school_id={$this->school->id}&as_of=2026-06-28")->json('data');
    expect($defaulters['count'])->toBe(1);
    expect($defaulters['students'][0]['student_id'])->toBe($this->s1->id);
});

// ---------------- Receipt + ledger + dashboard + gateway ----------------
it('builds receipt data with an identity QR', function (): void {
    assignFee($this->s1->id);
    $paymentId = $this->postJson('/api/v1/finance/payments', ['school_id' => $this->school->id, 'student_id' => $this->s1->id, 'amount' => 1000])->json('data.id');

    $receipt = $this->getJson("/api/v1/finance/payments/{$paymentId}/receipt")->assertOk()->json('data');
    expect($receipt['receipt_number'])->not->toBeNull();
    expect($receipt['identity']['qr_url'])->not->toBeNull();
    expect($receipt['breakdown'])->toHaveCount(1);
});

it('lists the ledger and returns the dashboard', function (): void {
    assignFee($this->s1->id);
    $this->postJson('/api/v1/finance/payments', ['school_id' => $this->school->id, 'student_id' => $this->s1->id, 'amount' => 1000])->assertCreated();

    $this->getJson("/api/v1/finance/ledger?filter[school_id]={$this->school->id}")->assertOk()->assertJsonCount(1, 'data');

    $this->getJson("/api/v1/finance/dashboard?school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['total_collection', 'outstanding_amount', 'todays_collection', 'monthly_collection', 'refunds', 'discounts', 'scholarships', 'defaulters'],
            'charts' => ['daily_collection', 'monthly_collection', 'category_collection', 'outstanding_trend'],
        ]]);
});

it('exposes the vendor-independent payment gateway abstraction', function (): void {
    $this->getJson('/api/v1/finance/gateways')->assertOk()->assertJsonPath('data.providers.0', 'manual');

    $this->postJson('/api/v1/finance/gateways/initiate', ['amount' => 500, 'student_id' => $this->s1->id])
        ->assertOk()->assertJsonPath('data.provider', 'manual')->assertJsonPath('data.status', 'created');
});
