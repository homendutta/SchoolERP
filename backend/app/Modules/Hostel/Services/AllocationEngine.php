<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Services;

use App\Modules\Hostel\Enums\AllocationStatus;
use App\Modules\Hostel\Enums\BedStatus;
use App\Modules\Hostel\Models\Allocation;
use App\Modules\Hostel\Models\Bed;
use App\Modules\Hostel\Models\Transfer;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Services\StudentTimelineService;
use App\Platform\Foundation\Audit\ActivityLogger;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Carbon;

/**
 * Bed allocation engine. Students occupy BEDS (never rooms directly); a bed can
 * never have two active occupants. Allocation history is never overwritten —
 * transfers and checkouts close the current record and create/free as needed.
 * Writes Timeline + Audit + a Communication event.
 */
class AllocationEngine extends BaseService
{
    public function __construct(
        private readonly StudentTimelineService $timeline,
        private readonly ActivityLogger $activity,
        private readonly HostelHooks $hooks,
    ) {}

    /**
     * @param  array{academic_year_id?:int|null}  $options
     */
    public function allocate(int $studentId, int $bedId, array $options = []): Allocation
    {
        return $this->transaction(function () use ($studentId, $bedId, $options): Allocation {
            $student = Student::query()->findOrFail($studentId);
            $bed = Bed::query()->with('room')->findOrFail($bedId);

            $this->guardBedFree($bed);
            $this->guardStudentNotAllocated($studentId);

            $allocation = $this->createAllocation($student, $bed, $options['academic_year_id'] ?? null);
            $bed->update(['status' => BedStatus::Occupied->value]);

            $this->timeline->record($student, 'hostel.allocated', 'Allocated a hostel bed', null, ['allocation_id' => $allocation->id]);
            $this->activity->record('hostel.allocated', "Bed {$bed->bed_number} allocated", $allocation, ['bed_id' => $bed->id], (int) $student->school_id, 'hostel');
            $this->hooks->allocation((int) $student->school_id, $student, "Allocated to bed {$bed->bed_number}.");

            return $allocation->refresh();
        });
    }

    /** Transfer to a new bed — closes the current allocation, frees the old bed. */
    public function transfer(int $studentId, int $toBedId, ?string $reason = null, ?string $transferType = null): Allocation
    {
        return $this->transaction(function () use ($studentId, $toBedId, $reason, $transferType): Allocation {
            $student = Student::query()->findOrFail($studentId);
            $current = Allocation::query()
                ->where('student_id', $studentId)
                ->where('status', AllocationStatus::Active->value)
                ->latest('id')->first();

            if ($current === null) {
                throw BusinessRuleException::make('The student has no active allocation to transfer.', 'NO_ACTIVE_ALLOCATION');
            }

            $toBed = Bed::query()->with('room')->findOrFail($toBedId);
            $this->guardBedFree($toBed);

            // Close current (history preserved) and free the old bed.
            $current->update(['status' => AllocationStatus::Transferred->value, 'checkout_date' => Carbon::now()->toDateString()]);
            Bed::query()->whereKey($current->bed_id)->update(['status' => BedStatus::Available->value]);

            $new = $this->createAllocation($student, $toBed, $current->academic_year_id);
            $toBed->update(['status' => BedStatus::Occupied->value]);

            Transfer::query()->create([
                'school_id' => $student->school_id,
                'student_id' => $studentId,
                'from_allocation_id' => $current->id,
                'to_allocation_id' => $new->id,
                'from_bed_id' => $current->bed_id,
                'to_bed_id' => $toBedId,
                'transfer_type' => $transferType ?? 'bed',
                'reason' => $reason,
                'transfer_date' => Carbon::now()->toDateString(),
            ]);

            $this->timeline->record($student, 'hostel.transferred', 'Transferred hostel bed', $reason, ['from' => $current->id, 'to' => $new->id]);
            $this->activity->record('hostel.transferred', "Transferred to bed {$toBed->bed_number}", $new, ['from_bed_id' => $current->bed_id, 'to_bed_id' => $toBedId], (int) $student->school_id, 'hostel');
            $this->hooks->roomTransfer((int) $student->school_id, $student, "Transferred to bed {$toBed->bed_number}.");

            return $new->refresh();
        });
    }

    public function checkout(Allocation $allocation): Allocation
    {
        return $this->transaction(function () use ($allocation): Allocation {
            $allocation->update(['status' => AllocationStatus::CheckedOut->value, 'checkout_date' => Carbon::now()->toDateString()]);
            Bed::query()->whereKey($allocation->bed_id)->update(['status' => BedStatus::Available->value]);

            $this->activity->record('hostel.checked_out', 'Hostel checkout', $allocation, [], (int) $allocation->school_id, 'hostel');

            return $allocation->refresh();
        });
    }

    private function createAllocation(Student $student, Bed $bed, ?int $academicYearId): Allocation
    {
        $room = $bed->room;

        return Allocation::query()->create([
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'academic_year_id' => $academicYearId,
            'hostel_id' => $room?->hostel_id,
            'building_id' => $room?->building_id,
            'floor_id' => $room?->floor_id,
            'room_id' => $bed->room_id,
            'bed_id' => $bed->id,
            'allocation_date' => Carbon::now()->toDateString(),
            'status' => AllocationStatus::Active->value,
        ]);
    }

    private function guardBedFree(Bed $bed): void
    {
        if (! $bed->status->isAllocatable()) {
            throw BusinessRuleException::make('This bed is not available.', 'BED_UNAVAILABLE');
        }

        $active = Allocation::query()->where('bed_id', $bed->id)->where('status', AllocationStatus::Active->value)->exists();
        if ($active) {
            throw BusinessRuleException::make('This bed already has an active occupant.', 'BED_OCCUPIED');
        }
    }

    private function guardStudentNotAllocated(int $studentId): void
    {
        $active = Allocation::query()->where('student_id', $studentId)->where('status', AllocationStatus::Active->value)->exists();
        if ($active) {
            throw BusinessRuleException::make('Student already has an active hostel allocation (use transfer).', 'ALREADY_ALLOCATED');
        }
    }
}
