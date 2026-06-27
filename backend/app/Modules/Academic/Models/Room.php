<?php

declare(strict_types=1);

namespace App\Modules\Academic\Models;

use App\Modules\Administration\Models\MasterDataValue;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id', 'room_type_id', 'code', 'name', 'capacity', 'building', 'display_order', 'status',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'display_order' => 'integer',
            'status' => RecordStatus::class,
        ];
    }

    /** Room type is Master Data (never hardcoded). */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(MasterDataValue::class, 'room_type_id');
    }
}
