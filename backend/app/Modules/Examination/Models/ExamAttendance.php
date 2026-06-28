<?php

declare(strict_types=1);

namespace App\Modules\Examination\Models;

use App\Modules\Examination\Enums\ExamAttendanceStatus;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Exam-day attendance (separate from daily attendance). */
class ExamAttendance extends Model
{
    protected $table = 'exam_attendances';

    protected $fillable = [
        'school_id', 'exam_schedule_id', 'student_id', 'status', 'remarks', 'recorded_by',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'present',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExamAttendanceStatus::class,
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
