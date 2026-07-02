<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Models;

use App\Modules\Administration\Models\MasterDataValue;
use App\Modules\Hostel\Enums\BedStatus;
use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A hostel room. Room type is Master Data; capacity is enforced. */
class Room extends Model
{
    use SoftDeletes;

    protected $table = 'hostel_rooms';

    protected $fillable = [
        'school_id', 'hostel_id', 'building_id', 'floor_id', 'room_number',
        'room_type_id', 'capacity', 'photo_media_id', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'available', 'capacity' => 1];

    protected function casts(): array
    {
        return ['capacity' => 'integer', 'status' => BedStatus::class];
    }

    /** Room type is Master Data (never hardcoded). */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(MasterDataValue::class, 'room_type_id');
    }

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class);
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_media_id');
    }
}
