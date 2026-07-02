<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Enums\AssignmentStatus;
use App\Modules\Inventory\Enums\AssignmentTarget;
use App\Platform\Foundation\Identity\Models\Identity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A historical asset assignment. Resolved through the Platform Identity Service
 * for person targets (identity_id + denormalized owner); non-person targets use
 * a decoupled target_reference — never a foreign key into another module. Never
 * overwritten.
 */
class AssetAssignment extends Model
{
    protected $table = 'asset_assignments';

    protected $fillable = [
        'school_id', 'asset_id', 'target_type', 'target_id', 'target_label', 'target_reference',
        'identity_id', 'owner_type', 'owner_id', 'assigned_staff_id',
        'assigned_on', 'returned_on', 'status', 'assigned_by',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return [
            'target_type' => AssignmentTarget::class,
            'status' => AssignmentStatus::class,
            'assigned_on' => 'date',
            'returned_on' => 'date',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /** The resolved Platform Identity (person targets). */
    public function identity(): BelongsTo
    {
        return $this->belongsTo(Identity::class);
    }

    /** The denormalized owner behind the Identity (e.g. Staff). */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
