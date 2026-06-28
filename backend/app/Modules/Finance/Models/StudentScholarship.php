<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A scholarship awarded to a student (complete history is preserved). */
class StudentScholarship extends Model
{
    protected $table = 'student_scholarships';

    protected $fillable = [
        'school_id', 'student_id', 'scholarship_id', 'student_fee_id', 'academic_year_id',
        'amount', 'awarded_on', 'notes', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return ['amount' => 'float', 'awarded_on' => 'date'];
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
