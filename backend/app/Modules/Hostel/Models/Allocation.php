<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Models;

use App\Modules\Hostel\Enums\AllocationStatus;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A student's bed allocation. History is never overwritten. */
class Allocation extends Model
{
    protected $table = 'hostel_allocations';

    protected $fillable = [
        'school_id', 'student_id', 'academic_year_id', 'hostel_id', 'building_id', 'floor_id',
        'room_id', 'bed_id', 'allocation_date', 'checkout_date', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return ['status' => AllocationStatus::class, 'allocation_date' => 'date', 'checkout_date' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }
}
