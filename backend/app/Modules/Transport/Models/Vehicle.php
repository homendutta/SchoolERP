<?php

declare(strict_types=1);

namespace App\Modules\Transport\Models;

use App\Modules\Transport\Enums\FuelType;
use App\Modules\Transport\Enums\VehicleStatus;
use App\Modules\Transport\Enums\VehicleType;
use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A transport vehicle. Number from the Number Generator; photo/docs are Media. */
class Vehicle extends Model
{
    use SoftDeletes;

    protected $table = 'transport_vehicles';

    protected $fillable = [
        'school_id', 'vehicle_number', 'registration_number', 'vehicle_type', 'manufacturer', 'model',
        'year', 'seating_capacity', 'reserved_seats', 'fuel_type', 'odometer', 'photo_media_id', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'vehicle_type' => 'bus', 'fuel_type' => 'diesel', 'seating_capacity' => 0, 'reserved_seats' => 0];

    protected function casts(): array
    {
        return [
            'vehicle_type' => VehicleType::class,
            'fuel_type' => FuelType::class,
            'status' => VehicleStatus::class,
            'year' => 'integer',
            'seating_capacity' => 'integer',
            'reserved_seats' => 'integer',
            'odometer' => 'integer',
        ];
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_media_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VehicleDocument::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(VehicleStaff::class);
    }

    /** Seats a vehicle offers to students (capacity minus reserved). */
    public function availableSeats(): int
    {
        return max(0, $this->seating_capacity - $this->reserved_seats);
    }
}
