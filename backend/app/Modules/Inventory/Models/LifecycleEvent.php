<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One immutable asset lifecycle transition (the asset Timeline). */
class LifecycleEvent extends Model
{
    public $timestamps = false;

    protected $table = 'asset_lifecycle_events';

    protected $fillable = ['school_id', 'asset_id', 'from_status', 'to_status', 'note', 'changed_by', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
