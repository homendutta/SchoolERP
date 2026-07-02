<?php

declare(strict_types=1);

namespace App\Modules\Library\Models;

use App\Modules\Library\Enums\ReservationStatus;
use App\Platform\Foundation\Identity\Models\Identity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** A queued reservation against a catalog title. Queue order is preserved. */
class Reservation extends Model
{
    protected $table = 'library_reservations';

    protected $fillable = [
        'school_id', 'identity_id', 'owner_type', 'owner_id', 'book_id',
        'status', 'queue_position', 'reserved_at', 'available_at', 'expires_at', 'fulfilled_borrowing_id',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'pending', 'queue_position' => 1];

    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'queue_position' => 'integer',
            'reserved_at' => 'datetime',
            'available_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function identity(): BelongsTo
    {
        return $this->belongsTo(Identity::class);
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
