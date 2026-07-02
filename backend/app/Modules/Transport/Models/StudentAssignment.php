<?php

declare(strict_types=1);

namespace App\Modules\Transport\Models;

use App\Modules\Students\Models\Student;
use App\Modules\Transport\Enums\AssignmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A student's assignment to a route + stop. Never deleted (history preserved). */
class StudentAssignment extends Model
{
    protected $table = 'transport_student_assignments';

    protected $fillable = [
        'school_id', 'student_id', 'route_id', 'stop_id', 'academic_year_id',
        'shift', 'status', 'assigned_on', 'ended_on',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return ['status' => AssignmentStatus::class, 'assigned_on' => 'date', 'ended_on' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function stop(): BelongsTo
    {
        return $this->belongsTo(Stop::class);
    }
}
