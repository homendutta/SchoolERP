<?php

declare(strict_types=1);

namespace App\Modules\Examination\Models;

use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Marks for a student in an exam subject (optionally per component). */
class ExamMark extends Model
{
    protected $table = 'exam_marks';

    protected $fillable = [
        'school_id', 'exam_subject_id', 'student_id', 'component_id',
        'marks_obtained', 'max_marks', 'is_absent', 'remarks', 'entered_by',
    ];

    protected function casts(): array
    {
        return [
            'marks_obtained' => 'float',
            'max_marks' => 'float',
            'is_absent' => 'boolean',
        ];
    }

    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(ExamComponent::class, 'component_id');
    }
}
