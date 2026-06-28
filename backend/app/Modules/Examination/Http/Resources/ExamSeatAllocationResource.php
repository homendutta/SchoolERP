<?php

declare(strict_types=1);

namespace App\Modules\Examination\Http\Resources;

use App\Modules\Examination\Models\ExamSeatAllocation;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin ExamSeatAllocation
 */
class ExamSeatAllocationResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'exam_schedule_id' => $this->exam_schedule_id,
            'room_id' => $this->room_id,
            'room' => $this->whenLoaded('room', fn () => $this->room?->name),
            'student_id' => $this->student_id,
            'student' => $this->whenLoaded('student', fn () => $this->student?->name),
            'seat_number' => $this->seat_number,
            'roll_number' => $this->roll_number,
        ];
    }
}
