<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Models;

use App\Modules\Hostel\Enums\BedStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A bed — the unit a student occupies (never a room directly). */
class Bed extends Model
{
    use SoftDeletes;

    protected $table = 'hostel_beds';

    protected $fillable = ['school_id', 'room_id', 'bed_number', 'bed_code', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'available'];

    protected function casts(): array
    {
        return ['status' => BedStatus::class];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
