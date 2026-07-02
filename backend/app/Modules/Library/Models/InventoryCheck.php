<?php

declare(strict_types=1);

namespace App\Modules\Library\Models;

use App\Modules\Library\Enums\InventoryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An inventory verification record for a copy. */
class InventoryCheck extends Model
{
    protected $table = 'library_inventory_checks';

    protected $fillable = ['school_id', 'copy_id', 'status', 'notes', 'checked_at', 'checked_by'];

    protected function casts(): array
    {
        return ['status' => InventoryStatus::class, 'checked_at' => 'datetime'];
    }

    public function copy(): BelongsTo
    {
        return $this->belongsTo(Copy::class);
    }
}
