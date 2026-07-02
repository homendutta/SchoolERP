<?php

declare(strict_types=1);

namespace App\Modules\Transport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A vehicle maintenance SCHEDULE entry (planning only; no workflow). */
class Maintenance extends Model
{
    use SoftDeletes;

    protected $table = 'transport_maintenance';

    protected $fillable = [
        'school_id', 'vehicle_id', 'service_type', 'service_due_date', 'odometer_due',
        'last_service_date', 'reminder_days', 'status', 'notes',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'scheduled', 'reminder_days' => 7];

    protected function casts(): array
    {
        return [
            'service_due_date' => 'date',
            'last_service_date' => 'date',
            'odometer_due' => 'integer',
            'reminder_days' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
