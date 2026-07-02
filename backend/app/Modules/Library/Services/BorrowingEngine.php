<?php

declare(strict_types=1);

namespace App\Modules\Library\Services;

use App\Modules\Library\Enums\BorrowStatus;
use App\Modules\Library\Enums\CopyStatus;
use App\Modules\Library\Enums\ReservationStatus;
use App\Modules\Library\Models\Borrowing;
use App\Modules\Library\Models\Copy;
use App\Modules\Library\Models\Renewal;
use App\Modules\Library\Models\Reservation;
use App\Modules\Staff\Models\Staff;
use App\Modules\Staff\Services\StaffTimelineService;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Services\StudentTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * The reusable Borrowing Engine. Borrowing always happens against a physical
 * copy (never the catalog). Validates borrower + availability, calculates due
 * dates, processes returns/renewals/reservations, computes fines (collection is
 * Finance's job), and writes Audit + Timeline + Communication events.
 */
class BorrowingEngine extends BaseService
{
    public function __construct(
        private readonly LibrarySettingsService $settings,
        private readonly FineCalculator $fines,
        private readonly LibraryHooks $hooks,
        private readonly ActivityLogger $activity,
        private readonly StudentTimelineService $studentTimeline,
        private readonly StaffTimelineService $staffTimeline,
    ) {}

    /** Borrow a physical copy for a resolved borrower. */
    public function borrow(Identity $identity, Copy $copy, ?int $issuedBy = null): Borrowing
    {
        return $this->transaction(function () use ($identity, $copy, $issuedBy): Borrowing {
            $owner = $identity->owner;
            if ($owner === null) {
                throw BusinessRuleException::make('Identity has no owner.', 'IDENTITY_NO_OWNER');
            }
            if (! $copy->status->isBorrowable()) {
                // Allow the front-of-queue reserver to borrow a Reserved copy.
                if (! ($copy->status === CopyStatus::Reserved && $this->isNextInQueue($identity, (int) $copy->book_id))) {
                    throw BusinessRuleException::make('This copy is not available to borrow.', 'COPY_UNAVAILABLE');
                }
            }

            $this->guardBorrowLimit($identity, (int) $copy->school_id);

            $days = $this->settings->borrowDaysFor($owner, (int) $copy->school_id);
            $borrowDate = Carbon::now()->toDateString();
            $dueDate = Carbon::now()->addDays($days)->toDateString();

            $borrowing = Borrowing::query()->create([
                'school_id' => $copy->school_id,
                'identity_id' => $identity->id,
                'owner_type' => $owner::class,
                'owner_id' => $owner->getKey(),
                'copy_id' => $copy->id,
                'book_id' => $copy->book_id,
                'borrow_date' => $borrowDate,
                'due_date' => $dueDate,
                'issued_by' => $issuedBy,
            ]);

            $copy->update(['status' => CopyStatus::Borrowed->value]);
            $this->fulfilReservationIfAny($identity, (int) $copy->book_id, $borrowing);

            $this->timeline($owner, 'library.borrowed', 'Borrowed a library book', ['borrowing_id' => $borrowing->id]);
            $this->activity->record('library.borrowed', "Copy {$copy->copy_number} borrowed", $borrowing, ['due_date' => $dueDate], $copy->school_id, 'library');

            return $borrowing->refresh();
        });
    }

    /** Return a borrowed copy. Never overwrites the borrowing — records return values. */
    public function returnCopy(Borrowing $borrowing, ?string $returnDate = null, ?string $damageNotes = null, ?int $receivedBy = null): Borrowing
    {
        return $this->transaction(function () use ($borrowing, $returnDate, $damageNotes, $receivedBy): Borrowing {
            if (! $borrowing->status->isOpen()) {
                throw BusinessRuleException::make('This borrowing is already closed.', 'ALREADY_RETURNED');
            }

            $date = $returnDate ?? Carbon::now()->toDateString();
            $fine = $this->fines->forReturn($borrowing, $date);

            $borrowing->update([
                'return_date' => $date,
                'late_days' => $fine['late_days'],
                'fine_amount' => $fine['fine'],
                'damage_notes' => $damageNotes,
                'status' => BorrowStatus::Returned->value,
                'returned_to' => $receivedBy,
            ]);

            $copy = Copy::query()->findOrFail($borrowing->copy_id);
            $copy->update(['status' => ($damageNotes !== null && $damageNotes !== '') ? CopyStatus::Damaged->value : CopyStatus::Available->value]);

            $this->timeline($borrowing->owner, 'library.returned', 'Returned a library book', ['borrowing_id' => $borrowing->id, 'fine' => $fine['fine']]);
            $this->activity->record('library.returned', "Copy {$copy->copy_number} returned", $borrowing, $fine, $borrowing->school_id, 'library');

            $this->promoteReservationQueue((int) $borrowing->book_id, $copy);

            return $borrowing->refresh();
        });
    }

    /** Renew a borrowing (extend due date). */
    public function renew(Borrowing $borrowing, ?int $renewedBy = null): Borrowing
    {
        return $this->transaction(function () use ($borrowing, $renewedBy): Borrowing {
            if (! $borrowing->status->isOpen()) {
                throw BusinessRuleException::make('Only an active borrowing can be renewed.', 'NOT_RENEWABLE');
            }
            if ($borrowing->copy->status->blocksRenewal()) {
                throw BusinessRuleException::make('This copy cannot be renewed (lost/damaged/withdrawn).', 'COPY_BLOCKS_RENEWAL');
            }
            if ($this->hasPendingReservations((int) $borrowing->book_id)) {
                throw BusinessRuleException::make('Cannot renew — the title is reserved by another borrower.', 'RESERVED');
            }
            $maxRenewals = $this->settings->get((int) $borrowing->school_id)->max_renewals;
            if ($borrowing->renewals_count >= $maxRenewals) {
                throw BusinessRuleException::make('Renewal limit reached.', 'RENEWAL_LIMIT');
            }

            $owner = $borrowing->owner;
            $previousDue = Carbon::parse($borrowing->due_date);
            $newDue = $previousDue->copy()->addDays($this->settings->borrowDaysFor($owner, (int) $borrowing->school_id));

            Renewal::query()->create([
                'borrowing_id' => $borrowing->id,
                'renewed_on' => Carbon::now()->toDateString(),
                'previous_due_date' => $previousDue->toDateString(),
                'new_due_date' => $newDue->toDateString(),
                'renewed_by' => $renewedBy,
            ]);

            $borrowing->update(['due_date' => $newDue->toDateString(), 'renewals_count' => $borrowing->renewals_count + 1]);

            $this->activity->record('library.renewed', 'Borrowing renewed', $borrowing, ['new_due_date' => $newDue->toDateString()], $borrowing->school_id, 'library');

            return $borrowing->refresh();
        });
    }

    /** Reserve a title; joins the queue preserving order. */
    public function reserve(Identity $identity, int $bookId, int $schoolId): Reservation
    {
        return $this->transaction(function () use ($identity, $bookId, $schoolId): Reservation {
            $owner = $identity->owner;
            if ($owner === null) {
                throw BusinessRuleException::make('Identity has no owner.', 'IDENTITY_NO_OWNER');
            }

            $position = Reservation::query()
                ->where('book_id', $bookId)
                ->whereIn('status', [ReservationStatus::Pending->value, ReservationStatus::Available->value])
                ->count() + 1;

            $reservation = Reservation::query()->create([
                'school_id' => $schoolId,
                'identity_id' => $identity->id,
                'owner_type' => $owner::class,
                'owner_id' => $owner->getKey(),
                'book_id' => $bookId,
                'status' => ReservationStatus::Pending->value,
                'queue_position' => $position,
                'reserved_at' => Carbon::now(),
            ]);

            $this->activity->record('library.reserved', 'Book reserved', $reservation, ['queue_position' => $position], $schoolId, 'library');

            return $reservation->refresh();
        });
    }

    private function guardBorrowLimit(Identity $identity, int $schoolId): void
    {
        $open = Borrowing::query()
            ->where('identity_id', $identity->id)
            ->whereIn('status', [BorrowStatus::Borrowed->value, BorrowStatus::Overdue->value])
            ->count();

        if ($open >= $this->settings->get($schoolId)->max_books_per_borrower) {
            throw BusinessRuleException::make('Borrowing limit reached for this borrower.', 'BORROW_LIMIT');
        }
    }

    private function hasPendingReservations(int $bookId): bool
    {
        return Reservation::query()->where('book_id', $bookId)->where('status', ReservationStatus::Pending->value)->exists();
    }

    private function isNextInQueue(Identity $identity, int $bookId): bool
    {
        $front = Reservation::query()
            ->where('book_id', $bookId)
            ->where('status', ReservationStatus::Available->value)
            ->orderBy('queue_position')
            ->first();

        return $front !== null && (int) $front->identity_id === (int) $identity->id;
    }

    private function fulfilReservationIfAny(Identity $identity, int $bookId, Borrowing $borrowing): void
    {
        $reservation = Reservation::query()
            ->where('book_id', $bookId)
            ->where('identity_id', $identity->id)
            ->where('status', ReservationStatus::Available->value)
            ->first();

        $reservation?->update(['status' => ReservationStatus::Fulfilled->value, 'fulfilled_borrowing_id' => $borrowing->id]);
    }

    /** When a copy frees up, offer it to the next reservation in the queue. */
    private function promoteReservationQueue(int $bookId, Copy $copy): void
    {
        $next = Reservation::query()
            ->where('book_id', $bookId)
            ->where('status', ReservationStatus::Pending->value)
            ->orderBy('queue_position')
            ->first();

        if ($next === null) {
            return;
        }

        $expiryDays = $this->settings->get((int) $copy->school_id)->reservation_expiry_days;
        $next->update([
            'status' => ReservationStatus::Available->value,
            'available_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addDays($expiryDays),
        ]);
        $copy->update(['status' => CopyStatus::Reserved->value]);

        $owner = $next->owner;
        if ($owner !== null) {
            $this->hooks->reservationAvailable((int) $copy->school_id, $owner, (string) $copy->book?->title);
        }
    }

    private function timeline(?Model $owner, string $event, string $title, array $meta): void
    {
        if ($owner instanceof Student) {
            $this->studentTimeline->record($owner, $event, $title, null, $meta);
        } elseif ($owner instanceof Staff) {
            $this->staffTimeline->record($owner, $event, $title, null, $meta);
        }
    }
}
