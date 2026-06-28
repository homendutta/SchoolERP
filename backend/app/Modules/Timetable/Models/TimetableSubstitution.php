<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Models;

use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Academic\Models\Subject;
use App\Modules\Staff\Models\Staff;
use App\Modules\Timetable\Enums\SubstitutionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A temporary teacher substitution. Separate record; never edits the master. */
class TimetableSubstitution extends Model
{
    use SoftDeletes;

    protected $table = 'timetable_substitutions';

    protected $fillable = [
        'school_id', 'class_timetable_id', 'academic_year_id',
        'original_teacher_id', 'substitute_teacher_id', 'date', 'period_id',
        'class_id', 'section_id', 'subject_id', 'reason', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'planned',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => SubstitutionStatus::class,
        ];
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(ClassTimetable::class, 'class_timetable_id');
    }

    public function originalTeacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'original_teacher_id');
    }

    public function substituteTeacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'substitute_teacher_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(TimetablePeriod::class, 'period_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
