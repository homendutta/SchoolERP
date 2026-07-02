<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Models;

use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A room/bed/building/hostel transfer event. Full history preserved. */
class Transfer extends Model
{
    protected $table = 'hostel_transfers';

    protected $fillable = [
        'school_id', 'student_id', 'from_allocation_id', 'to_allocation_id',
        'from_bed_id', 'to_bed_id', 'transfer_type', 'reason', 'transfer_date', 'performed_by',
    ];

    protected function casts(): array
    {
        return ['transfer_date' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
