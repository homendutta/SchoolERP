<?php

declare(strict_types=1);

use App\Modules\Administration\Models\School;
use App\Modules\Administration\Models\User;
use App\Modules\Finance\Models\Payment;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Staff\Models\Staff;
use App\Modules\Students\Models\Student;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);

    // A parent with two linked children.
    $this->parentUser = User::create(['name' => 'Parent', 'email' => 'p@x.test', 'username' => 'parent1', 'password' => 'Password@123', 'status' => 'active', 'school_id' => $this->school->id]);
    $this->guardian = Guardian::create(['school_id' => $this->school->id, 'user_id' => $this->parentUser->id, 'name' => 'Parent', 'relation' => 'father']);
    $this->child1 = Student::create(['school_id' => $this->school->id, 'admission_number' => 'A1', 'name' => 'Child One', 'status' => 'active']);
    $this->child2 = Student::create(['school_id' => $this->school->id, 'admission_number' => 'A2', 'name' => 'Child Two', 'status' => 'active']);
    foreach ([$this->child1, $this->child2] as $c) {
        DB::table('student_guardian')->insert(['student_id' => $c->id, 'guardian_id' => $this->guardian->id, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()]);
    }

    // An unrelated student (isolation target).
    $this->outsider = Student::create(['school_id' => $this->school->id, 'admission_number' => 'A9', 'name' => 'Outsider', 'status' => 'active']);

    // A self-service student.
    $this->studentUser = User::create(['name' => 'Stud', 'email' => 's@x.test', 'username' => 'stud1', 'password' => 'Password@123', 'status' => 'active', 'school_id' => $this->school->id]);
    $this->selfStudent = Student::create(['school_id' => $this->school->id, 'user_id' => $this->studentUser->id, 'admission_number' => 'A3', 'name' => 'Self Student', 'status' => 'active']);

    // A teacher.
    $this->teacherUser = User::create(['name' => 'Teach', 'email' => 't@x.test', 'username' => 'teach1', 'password' => 'Password@123', 'status' => 'active', 'school_id' => $this->school->id]);
    $this->staff = Staff::create(['school_id' => $this->school->id, 'user_id' => $this->teacherUser->id, 'employee_number' => 'E1', 'name' => 'Teacher', 'status' => 'active']);
});

// ---------------- Auth + role resolution ----------------
it('logs in a portal user and returns their role', function (): void {
    $this->postJson('/api/v1/portal/login', ['identifier' => 'parent1', 'password' => 'Password@123'])
        ->assertOk()->assertJsonPath('data.role', 'parent')->assertJsonStructure(['data' => ['token']]);
});

it('resolves the parent context with only their children', function (): void {
    Sanctum::actingAs($this->parentUser);

    $this->getJson('/api/v1/portal/me')
        ->assertOk()->assertJsonPath('data.role', 'parent')->assertJsonCount(2, 'data.students');
});

// ---------------- Parent isolation ----------------
it('lets a parent read a linked child but blocks an unrelated student', function (): void {
    Sanctum::actingAs($this->parentUser);

    $this->getJson("/api/v1/portal/attendance?student_id={$this->child1->id}")->assertOk();
    $this->getJson("/api/v1/portal/attendance?student_id={$this->outsider->id}")->assertStatus(403);
});

// ---------------- Student isolation ----------------
it('lets a student read only their own records', function (): void {
    Sanctum::actingAs($this->studentUser);

    $this->getJson('/api/v1/portal/me')->assertOk()->assertJsonPath('data.role', 'student');
    $this->getJson("/api/v1/portal/attendance?student_id={$this->selfStudent->id}")->assertOk();
    $this->getJson("/api/v1/portal/attendance?student_id={$this->child1->id}")->assertStatus(403);
});

// ---------------- Online fee payment (reuses Finance engine) ----------------
it('lets a parent pay multiple children in one transaction', function (): void {
    Sanctum::actingAs($this->parentUser);

    $res = $this->postJson('/api/v1/portal/fees/pay', [
        'gateway' => 'manual',
        'items' => [
            ['student_id' => $this->child1->id, 'amount' => 100],
            ['student_id' => $this->child2->id, 'amount' => 200],
        ],
    ])->assertCreated()->assertJsonPath('data.total', 300)->json('data');

    expect($res['payments'])->toHaveCount(2);
    expect(Payment::where('student_id', $this->child1->id)->count())->toBe(1);
    expect(Payment::where('student_id', $this->child2->id)->count())->toBe(1);
    // Each child's payment is its own Finance receipt (Finance is the source of truth).
    $this->assertDatabaseHas('activity_logs', ['action' => 'finance.payment_recorded']);
    $this->assertDatabaseHas('student_timelines', ['student_id' => $this->child1->id, 'event_type' => 'finance.payment_received']);
});

it('blocks a parent from paying an unrelated student', function (): void {
    Sanctum::actingAs($this->parentUser);

    $this->postJson('/api/v1/portal/fees/pay', ['items' => [['student_id' => $this->outsider->id, 'amount' => 50]]])
        ->assertStatus(403);
    expect(Payment::where('student_id', $this->outsider->id)->count())->toBe(0);
});

it('lets a student pay only their own fees and download a receipt', function (): void {
    Sanctum::actingAs($this->studentUser);

    $data = $this->postJson('/api/v1/portal/fees/pay', ['items' => [['student_id' => $this->selfStudent->id, 'amount' => 150]]])
        ->assertCreated()->json('data');
    $paymentId = $data['payments'][0]['payment_id'];

    $this->getJson("/api/v1/portal/fees/receipt/{$paymentId}")->assertOk();

    // Cannot pay someone else.
    $this->postJson('/api/v1/portal/fees/pay', ['items' => [['student_id' => $this->child1->id, 'amount' => 10]]])->assertStatus(403);
});

// ---------------- Teacher RBAC (no finance access) ----------------
it('denies teachers any fee access', function (): void {
    Sanctum::actingAs($this->teacherUser);

    $this->getJson('/api/v1/portal/me')->assertOk()->assertJsonPath('data.role', 'teacher');
    $this->getJson("/api/v1/portal/fees?student_id={$this->child1->id}")->assertStatus(403);
    $this->getJson('/api/v1/portal/payment-gateways')->assertStatus(403);
});

it('lists payment gateway providers for a payer', function (): void {
    Sanctum::actingAs($this->parentUser);

    $this->getJson('/api/v1/portal/payment-gateways')
        ->assertOk()->assertJsonPath('data.providers.0', 'manual');
});

// ---------------- Dashboard + messages + downloads ----------------
it('returns a role-aware parent dashboard and shared feeds', function (): void {
    Sanctum::actingAs($this->parentUser);

    $this->getJson('/api/v1/portal/dashboard')
        ->assertOk()->assertJsonPath('data.role', 'parent')->assertJsonPath('data.widgets.children', 2);

    $this->getJson('/api/v1/portal/messages')->assertOk()->assertJsonStructure(['data' => ['announcements', 'circulars']]);
    $this->getJson('/api/v1/portal/downloads')->assertOk();
});

// ---------------- Unauthenticated ----------------
it('rejects unauthenticated portal access', function (): void {
    $this->getJson('/api/v1/portal/dashboard')->assertStatus(401);
});
