<?php

declare(strict_types=1);

namespace App\Modules\Examination\Models;

use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Authoritative record that a student is assigned a subject for a session. The
 * presence (or absence) of a row here drives optional/elective correctness
 * across marks, results, report cards and promotion.
 */
class ExamStudentSubject extends Model
{
    protected $table = 'exam_student_subjects';

    protected $fillable = [
        'school_id', 'exam_session_id', 'exam_subject_id', 'student_id', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'active',
    ];

    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
