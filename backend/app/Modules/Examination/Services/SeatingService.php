<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Academic\Models\Room;
use App\Modules\Examination\Models\ExamSeatAllocation;
use App\Platform\Shared\Exceptions\BusinessRuleException;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Seating plan with room-capacity validation (capacity is never exceeded). */
class SeatingService extends BaseCrudService
{
    protected function model(): string
    {
        return ExamSeatAllocation::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'student:id,name,admission_number',
            'room:id,name,capacity',
        ]);
    }

    protected function filterable(): array
    {
        return ['school_id', 'exam_schedule_id', 'room_id', 'student_id'];
    }

    protected function sortable(): array
    {
        return ['id', 'seat_number', 'roll_number'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        $this->assertCapacity((int) $data['room_id'], (int) $data['exam_schedule_id']);

        return ExamSeatAllocation::query()->updateOrCreate(
            ['exam_schedule_id' => $data['exam_schedule_id'], 'student_id' => $data['student_id']],
            $data,
        );
    }

    /** Room capacity (reused from Academic) must never be exceeded. */
    private function assertCapacity(int $roomId, int $scheduleId): void
    {
        $room = Room::query()->find($roomId);
        $capacity = (int) ($room?->capacity ?? 0);
        if ($capacity <= 0) {
            return; // capacity not set → unconstrained
        }

        $allocated = ExamSeatAllocation::query()
            ->where('exam_schedule_id', $scheduleId)
            ->where('room_id', $roomId)
            ->count();

        if ($allocated >= $capacity) {
            throw BusinessRuleException::make("Room capacity ({$capacity}) reached for this exam.", 'ROOM_CAPACITY_EXCEEDED');
        }
    }
}
