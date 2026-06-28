<?php

declare(strict_types=1);

namespace App\Modules\Students\Models;

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Platform\Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable per-year placement of a student. Promotion creates a NEW record and
 * flips is_current; a student's class is never updated in place.
 */
class StudentAcademicRecord extends Model
{
    use HasUuid;

    protected $fillable = [
        'school_id', 'student_id', 'academic_year_id', 'class_id', 'section_id',
        'roll_number', 'admission_number', 'promoted_from_record_id',
        'status', 'is_current', 'started_on', 'ended_on',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'started_on' => 'date',
            'ended_on' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
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

    public function promotedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'promoted_from_record_id');
    }
}
