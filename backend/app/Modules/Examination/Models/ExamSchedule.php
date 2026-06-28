<?php

declare(strict_types=1);

namespace App\Modules\Examination\Models;

use App\Modules\Academic\Models\Room;
use App\Modules\Timetable\Models\TimetablePeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A scheduled exam for one subject (date + period + room + duration). */
class ExamSchedule extends Model
{
    use SoftDeletes;

    protected $table = 'exam_schedules';

    protected $fillable = [
        'school_id', 'exam_session_id', 'exam_subject_id', 'exam_date',
        'period_id', 'start_time', 'room_id', 'duration_minutes', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'scheduled',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'duration_minutes' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(TimetablePeriod::class, 'period_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function invigilators(): HasMany
    {
        return $this->hasMany(ExamInvigilator::class);
    }

    public function seatAllocations(): HasMany
    {
        return $this->hasMany(ExamSeatAllocation::class);
    }
}
