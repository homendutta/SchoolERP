<?php

declare(strict_types=1);

namespace App\Modules\Transport\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A transport route with ordered stops. Route code from the Number Generator. */
class TransportRoute extends Model
{
    use SoftDeletes;

    protected $table = 'transport_routes';

    protected $fillable = [
        'school_id', 'route_code', 'name', 'start_location', 'end_location',
        'distance_km', 'estimated_minutes', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return ['distance_km' => 'float', 'estimated_minutes' => 'integer', 'status' => RecordStatus::class];
    }

    public function stops(): HasMany
    {
        return $this->hasMany(Stop::class, 'route_id')->orderBy('sequence');
    }
}
