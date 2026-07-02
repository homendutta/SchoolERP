<?php

declare(strict_types=1);

namespace App\Modules\Transport\Models;

use App\Modules\Staff\Models\Staff;
use App\Modules\Transport\Enums\VehicleStaffRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A Staff member assigned to a vehicle as driver/backup/attendant/helper. */
class VehicleStaff extends Model
{
    protected $table = 'transport_vehicle_staff';

    protected $fillable = ['school_id', 'vehicle_id', 'staff_id', 'role', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return ['role' => VehicleStaffRole::class];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
