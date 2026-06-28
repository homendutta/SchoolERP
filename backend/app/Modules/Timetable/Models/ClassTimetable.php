<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Models;

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\Room;
use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Academic\Models\Subject;
use App\Modules\Staff\Models\Staff;
use App\Modules\Timetable\Enums\Weekday;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A single master timetable slot. Teacher and Room timetables are derived from
 * these rows — they are never stored separately.
 */
class ClassTimetable extends Model
{
    use SoftDeletes;

    protected $table = 'class_timetables';

    protected $fillable = [
        'school_id', 'template_id', 'academic_year_id', 'class_id', 'section_id',
        'weekday', 'period_id', 'subject_id', 'teacher_id', 'room_id', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'weekday' => Weekday::class,
            'status' => RecordStatus::class,
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TimetableTemplate::class, 'template_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(TimetablePeriod::class, 'period_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /** The teacher is a Staff member (reused from Staff Management). */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'teacher_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
