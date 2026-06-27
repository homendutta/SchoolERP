<?php

declare(strict_types=1);

namespace App\Modules\Academic\Models;

use App\Modules\Administration\Models\User;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherSubjectAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'academic_year_id', 'class_id', 'section_id', 'subject_id', 'teacher_id', 'is_primary', 'status',
    ];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean', 'status' => RecordStatus::class];
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

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
