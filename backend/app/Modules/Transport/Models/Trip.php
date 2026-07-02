<?php

declare(strict_types=1);

namespace App\Modules\Transport\Models;

use App\Modules\Staff\Models\Staff;
use App\Modules\Transport\Enums\TripShift;
use App\Modules\Transport\Enums\TripStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A scheduled trip: a route run by a vehicle + driver for a shift. */
class Trip extends Model
{
    use SoftDeletes;

    protected $table = 'transport_trips';

    protected $fillable = [
        'school_id', 'vehicle_id', 'route_id', 'driver_id', 'attendant_id',
        'academic_year_id', 'shift', 'departure_time', 'arrival_time', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'scheduled', 'shift' => 'morning'];

    protected function casts(): array
    {
        return ['shift' => TripShift::class, 'status' => TripStatus::class];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'driver_id');
    }

    public function attendant(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'attendant_id');
    }
}
