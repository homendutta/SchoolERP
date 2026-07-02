<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Floor extends Model
{
    use SoftDeletes;

    protected $table = 'hostel_floors';

    protected $fillable = ['school_id', 'building_id', 'floor_number', 'name', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'floor_number' => 0];

    protected function casts(): array
    {
        return ['floor_number' => 'integer', 'status' => RecordStatus::class];
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }
}
