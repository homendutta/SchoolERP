<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Maintenance\Models;

use App\Platform\Foundation\Maintenance\Enums\MaintenancePriority;
use App\Platform\Foundation\Maintenance\Enums\MaintenanceStatus;
use App\Platform\Foundation\Maintenance\Enums\MaintenanceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A maintenance request against any polymorphic maintainable. */
class MaintenanceRequest extends Model
{
    use SoftDeletes;

    protected $table = 'maintenance_requests';

    protected $fillable = [
        'school_id', 'maintainable_type', 'maintainable_id', 'type', 'priority',
        'assigned_staff_id', 'scheduled_date', 'completed_date', 'cost', 'notes', 'status', 'requested_by',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'scheduled', 'type' => 'preventive', 'priority' => 'medium'];

    protected function casts(): array
    {
        return [
            'type' => MaintenanceType::class,
            'priority' => MaintenancePriority::class,
            'status' => MaintenanceStatus::class,
            'scheduled_date' => 'date',
            'completed_date' => 'date',
            'cost' => 'float',
        ];
    }

    public function maintainable(): MorphTo
    {
        return $this->morphTo();
    }
}
