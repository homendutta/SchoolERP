<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    use SoftDeletes;

    protected $table = 'hostel_buildings';

    protected $fillable = ['school_id', 'hostel_id', 'name', 'code', 'floors_count', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'floors_count' => 0];

    protected function casts(): array
    {
        return ['floors_count' => 'integer', 'status' => RecordStatus::class];
    }

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    public function floors(): HasMany
    {
        return $this->hasMany(Floor::class);
    }
}
