<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Models;

use App\Modules\Hostel\Enums\WardenRole;
use App\Modules\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A warden assignment (Staff → hostel). History preserved. */
class Warden extends Model
{
    protected $table = 'hostel_wardens';

    protected $fillable = ['school_id', 'hostel_id', 'staff_id', 'role', 'assigned_on', 'ended_on', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'role' => 'chief'];

    protected function casts(): array
    {
        return ['role' => WardenRole::class, 'assigned_on' => 'date', 'ended_on' => 'date'];
    }

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
