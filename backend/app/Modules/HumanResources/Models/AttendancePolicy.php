<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Attendance policy DEFINED by HR and CONSUMED by the Attendance module. HR never
 * duplicates attendance logic — it only configures the rules.
 */
class AttendancePolicy extends Model
{
    use SoftDeletes;

    protected $table = 'hr_attendance_policies';

    protected $fillable = [
        'school_id', 'name', 'grace_minutes', 'half_day_hours', 'late_after_minutes',
        'overtime_eligible', 'minimum_working_hours', 'description', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return [
            'grace_minutes' => 'integer',
            'half_day_hours' => 'decimal:2',
            'late_after_minutes' => 'integer',
            'overtime_eligible' => 'boolean',
            'minimum_working_hours' => 'decimal:2',
            'status' => RecordStatus::class,
        ];
    }
}
