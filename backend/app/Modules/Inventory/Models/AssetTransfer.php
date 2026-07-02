<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A historical asset transfer event. */
class AssetTransfer extends Model
{
    protected $table = 'asset_transfers';

    protected $fillable = [
        'school_id', 'asset_id', 'from_assignment_id', 'to_assignment_id',
        'from_label', 'to_label', 'transfer_type', 'reason', 'transfer_date', 'performed_by',
    ];

    protected function casts(): array
    {
        return ['transfer_date' => 'date'];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
