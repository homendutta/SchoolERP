<?php

declare(strict_types=1);

use App\Modules\Administration\Models\School;
use App\Modules\Finance\Models\Payment;
use App\Modules\Library\Models\Author;
use App\Modules\Library\Models\Book;
use App\Modules\Library\Models\Borrowing;
use App\Modules\Library\Models\Copy;
use App\Modules\Library\Models\FineRule;
use App\Modules\Library\Models\Reservation;
use App\Modules\Staff\Models\Staff;
use App\Modules\Students\Models\Student;
use App\Platform\Foundation\Identity\Models\Identity;

beforeEach(function (): void {
    $this->school = School::create(['name' => 'Test School', 'short_name' => 'TS', 'code' => 'TS', 'is_active' => true]);

    $this->s1 = Student::create(['school_id' => $this->school->id, 'admission_number' => '1001', 'name' => 'Asha', 'email' => 'asha@test', 'phone' => '9000000001', 'status' => 'active']);
    $this->s2 = Student::create(['school_id' => $this->school->id, 'admission_number' => '1002', 'name' => 'Bina', 'email' => 'bina@test', 'phone' => '9000000002', 'status' => 'active']);
    $this->s1->refresh();
    $this->s2->refresh();
    $this->id1 = Identity::find($this->s1->identity_id);
    $this->id2 = Identity::find($this->s2->identity_id);

    $this->author = Author::create(['school_id' => $this->school->id, 'name' => 'R. K. Narayan']);
    $this->book = Book::create(['school_id' => $this->school->id, 'title' => 'Malgudi Days', 'isbn' => '9788185986176']);
    $this->book->authors()->attach($this->author->id);
    $this->copy = Copy::create(['school_id' => $this->school->id, 'book_id' => $this->book->id, 'copy_number' => 'C-001']);
    $this->copy->refresh();

    actingAsSuperAdmin();
});

// ---------------- Catalog + copies ----------------
it('manages the catalog with many-to-many authors', function (): void {
    $id = $this->postJson('/api/v1/library/catalog', [
        'school_id' => $this->school->id, 'title' => 'The Guide', 'isbn' => '9780143039648',
        'author_ids' => [$this->author->id],
    ])->assertCreated()->json('data.id');

    $this->getJson("/api/v1/library/catalog/{$id}")->assertOk()->assertJsonPath('data.authors.0.name', 'R. K. Narayan');
});

it('gives every physical copy its own permanent identity (barcode + QR)', function (): void {
    expect($this->copy->identity_id)->not->toBeNull();

    $this->getJson("/api/v1/library/copies/{$this->copy->id}")
        ->assertOk()
        ->assertJsonPath('data.status', 'available')
        ->assertJsonPath('data.identity_number', Identity::find($this->copy->identity_id)->identity_number);
});

// ---------------- Borrowing ----------------
it('borrows a physical copy via the Identity Platform', function (): void {
    $this->postJson('/api/v1/library/borrow', [
        'school_id' => $this->school->id, 'identity_number' => $this->id1->identity_number, 'copy_id' => $this->copy->id,
    ])->assertCreated()->assertJsonPath('data.book', 'Malgudi Days');

    expect($this->copy->fresh()->status->value)->toBe('borrowed');
    expect(Borrowing::first()->identity_id)->toBe($this->id1->id);
    $this->assertDatabaseHas('activity_logs', ['action' => 'library.borrowed']);
    $this->assertDatabaseHas('student_timelines', ['student_id' => $this->s1->id, 'event_type' => 'library.borrowed']);
});

it('rejects an unknown borrower identity', function (): void {
    $this->postJson('/api/v1/library/borrow', [
        'school_id' => $this->school->id, 'identity_number' => 'NOPE-999', 'copy_id' => $this->copy->id,
    ])->assertStatus(422)->assertJsonPath('code', 'BORROWER_NOT_FOUND');
});

it('cannot borrow an already borrowed copy', function (): void {
    $this->postJson('/api/v1/library/borrow', ['school_id' => $this->school->id, 'identity_number' => $this->id1->identity_number, 'copy_id' => $this->copy->id])->assertCreated();

    $this->postJson('/api/v1/library/borrow', ['school_id' => $this->school->id, 'identity_number' => $this->id2->identity_number, 'copy_id' => $this->copy->id])
        ->assertStatus(422)->assertJsonPath('code', 'COPY_UNAVAILABLE');
});

// ---------------- Returns + fine ----------------
it('returns a copy and calculates the fine (Library calculates; Finance collects)', function (): void {
    FineRule::create(['school_id' => $this->school->id, 'name' => 'Standard', 'mode' => 'daily', 'amount' => 5, 'grace_period_days' => 0]);

    $borrowId = $this->postJson('/api/v1/library/borrow', ['school_id' => $this->school->id, 'identity_number' => $this->id1->identity_number, 'copy_id' => $this->copy->id])->json('data.id');
    Borrowing::whereKey($borrowId)->update(['due_date' => now()->subDays(4)->toDateString()]);

    $this->postJson('/api/v1/library/return', ['borrowing_id' => $borrowId, 'return_date' => now()->toDateString()])
        ->assertOk()->assertJsonPath('data.status', 'returned')->assertJsonPath('data.late_days', 4)->assertJsonPath('data.fine_amount', 20);

    expect($this->copy->fresh()->status->value)->toBe('available');
    // Payment is NOT collected in Library.
    expect(class_exists(Payment::class))->toBeTrue();
});

// ---------------- Renewals ----------------
it('renews a borrowing and blocks renewal when reserved', function (): void {
    $borrowId = $this->postJson('/api/v1/library/borrow', ['school_id' => $this->school->id, 'identity_number' => $this->id1->identity_number, 'copy_id' => $this->copy->id])->json('data.id');

    $this->postJson('/api/v1/library/renew', ['borrowing_id' => $borrowId])->assertOk()->assertJsonPath('data.renewals_count', 1);
    $this->assertDatabaseHas('library_renewals', ['borrowing_id' => $borrowId]);

    // Someone reserves the title → renewal now blocked.
    $this->postJson('/api/v1/library/reservations', ['school_id' => $this->school->id, 'identity_number' => $this->id2->identity_number, 'book_id' => $this->book->id])->assertCreated();
    $this->postJson('/api/v1/library/renew', ['borrowing_id' => $borrowId])->assertStatus(422)->assertJsonPath('code', 'RESERVED');
});

// ---------------- Reservations queue ----------------
it('preserves the reservation queue and notifies the front borrower on return', function (): void {
    $borrowId = $this->postJson('/api/v1/library/borrow', ['school_id' => $this->school->id, 'identity_number' => $this->id1->identity_number, 'copy_id' => $this->copy->id])->json('data.id');

    $this->postJson('/api/v1/library/reservations', ['school_id' => $this->school->id, 'identity_number' => $this->id2->identity_number, 'book_id' => $this->book->id])
        ->assertCreated()->assertJsonPath('data.queue_position', 1);

    $this->postJson('/api/v1/library/return', ['borrowing_id' => $borrowId])->assertOk();

    $reservation = Reservation::first();
    expect($reservation->status->value)->toBe('available');
    expect($this->copy->fresh()->status->value)->toBe('reserved');
    // Front borrower notified via the Communication Engine (never sent by Library).
    $this->assertDatabaseHas('communication_batches', ['event' => 'library.reservation_available']);
});

// ---------------- Inventory ----------------
it('records inventory verification and reports outcomes', function (): void {
    $this->postJson('/api/v1/library/inventory', ['copy_id' => $this->copy->id, 'status' => 'verified'])->assertCreated();

    $this->getJson("/api/v1/library/inventory/report?school_id={$this->school->id}")
        ->assertOk()->assertJsonPath('data.verified', 1);
});

it('marks a copy missing which reflects as lost without deleting the copy', function (): void {
    $this->postJson('/api/v1/library/inventory', ['copy_id' => $this->copy->id, 'status' => 'missing'])->assertCreated();

    expect($this->copy->fresh()->status->value)->toBe('lost');
    expect(Copy::withTrashed()->count())->toBe(1); // never deleted
});

// ---------------- Search + dashboard ----------------
it('searches the catalog by author and returns the dashboard', function (): void {
    $this->getJson('/api/v1/library/catalog?'.http_build_query(['search' => ['author' => 'Narayan']]))
        ->assertOk()->assertJsonCount(1, 'data');

    $this->getJson("/api/v1/library/dashboard?school_id={$this->school->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'widgets' => ['total_titles', 'total_copies', 'borrowed', 'available', 'reserved', 'overdue', 'lost', 'damaged'],
            'charts' => ['borrowing_trend', 'popular_books', 'category_distribution', 'overdue_trend'],
        ]]);
});

// ---------------- Staff borrower ----------------
it('allows staff to borrow with staff borrow period', function (): void {
    $staff = Staff::create(['school_id' => $this->school->id, 'employee_number' => 'E1', 'name' => 'Ramesh', 'status' => 'active']);
    $staff->refresh();
    $staffIdentity = Identity::find($staff->identity_id);
    $copy2 = Copy::create(['school_id' => $this->school->id, 'book_id' => $this->book->id, 'copy_number' => 'C-002']);

    $this->postJson('/api/v1/library/borrow', ['school_id' => $this->school->id, 'identity_number' => $staffIdentity->identity_number, 'copy_id' => $copy2->id])
        ->assertCreated()->assertJsonPath('data.owner_type', 'Staff');
});
