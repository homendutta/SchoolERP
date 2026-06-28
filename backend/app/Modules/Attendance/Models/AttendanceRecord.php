<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Models;

use App\Modules\Administration\Models\MasterDataValue;
use App\Modules\Attendance\Enums\AttendanceSource;
use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One attendance record, identity-based, serving students and staff alike.
 */
class AttendanceRecord extends Model
{
    use HasUuid;

    protected $table = 'attendance_records';

    protected $fillable = [
        'school_id', 'identity_id', 'owner_type', 'owner_id',
        'academic_year_id', 'class_id', 'section_id', 'department_id', 'designation_id',
        'session_id', 'shift', 'attendance_date', 'status', 'source',
        'check_in_time', 'check_out_time', 'is_late', 'remarks',
        'biometric_log_id', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'is_late' => 'boolean',
            'status' => AttendanceStatus::class,
            'source' => AttendanceSource::class,
        ];
    }

    public function identity(): BelongsTo
    {
        return $this->belongsTo(Identity::class);
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(MasterDataValue::class, 'session_id');
    }
}
