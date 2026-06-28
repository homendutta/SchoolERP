<?php

declare(strict_types=1);

namespace App\Modules\Examination\Models;

use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Examination\Enums\ResultStatus;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Processed aggregate result for a student in a session. */
class ExamResult extends Model
{
    protected $table = 'exam_results';

    protected $fillable = [
        'school_id', 'exam_session_id', 'student_id', 'class_id', 'section_id',
        'total_obtained', 'total_max', 'percentage', 'grade_id', 'gpa',
        'result_status', 'rank', 'subjects_count', 'failed_count', 'is_published', 'published_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'result_status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'total_obtained' => 'float',
            'total_max' => 'float',
            'percentage' => 'float',
            'gpa' => 'float',
            'rank' => 'integer',
            'subjects_count' => 'integer',
            'failed_count' => 'integer',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'result_status' => ResultStatus::class,
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(ExamGrade::class, 'grade_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
