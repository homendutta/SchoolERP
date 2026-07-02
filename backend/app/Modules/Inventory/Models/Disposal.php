<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Enums\DisposalMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An asset disposal record. Disposed assets are never deleted. */
class Disposal extends Model
{
    protected $table = 'asset_disposals';

    protected $fillable = ['school_id', 'asset_id', 'method', 'reason', 'disposal_date', 'value', 'approved_by'];

    protected function casts(): array
    {
        return ['method' => DisposalMethod::class, 'disposal_date' => 'date', 'value' => 'float'];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
