<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Models;

use App\Modules\Hostel\Enums\MaintenanceCategory;
use App\Modules\Hostel\Enums\MaintenancePriority;
use App\Modules\Hostel\Enums\MaintenanceStatus;
use App\Modules\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A hostel maintenance request (no workflow engine). */
class Maintenance extends Model
{
    use SoftDeletes;

    protected $table = 'hostel_maintenance';

    protected $fillable = [
        'school_id', 'hostel_id', 'room_id', 'reported_by', 'category', 'priority',
        'description', 'status', 'assigned_staff_id', 'resolution_date', 'notes',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'open', 'category' => 'other', 'priority' => 'medium'];

    protected function casts(): array
    {
        return [
            'category' => MaintenanceCategory::class,
            'priority' => MaintenancePriority::class,
            'status' => MaintenanceStatus::class,
            'resolution_date' => 'date',
        ];
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_staff_id');
    }

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }
}
