<?php

declare(strict_types=1);

namespace App\Modules\Examination\Models;

use App\Modules\Academic\Models\Room;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A student's seat in a room for a scheduled exam. */
class ExamSeatAllocation extends Model
{
    protected $table = 'exam_seat_allocations';

    protected $fillable = [
        'school_id', 'exam_schedule_id', 'room_id', 'student_id', 'seat_number', 'roll_number',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
