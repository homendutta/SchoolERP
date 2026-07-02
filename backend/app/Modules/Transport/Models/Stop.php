<?php

declare(strict_types=1);

namespace App\Modules\Transport\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A stop on a route. Lat/long stored future-ready for GPS. */
class Stop extends Model
{
    use SoftDeletes;

    protected $table = 'transport_stops';

    protected $fillable = [
        'school_id', 'route_id', 'name', 'code', 'sequence',
        'pickup_time', 'drop_time', 'latitude', 'longitude', 'capacity', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'sequence' => 0];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'capacity' => 'integer',
            'status' => RecordStatus::class,
        ];
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }
}
