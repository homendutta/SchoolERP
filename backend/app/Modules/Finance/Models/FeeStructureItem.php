<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A Fee Master included in a Fee Structure (with optional amount override). */
class FeeStructureItem extends Model
{
    protected $table = 'fee_structure_items';

    protected $fillable = ['fee_structure_id', 'fee_master_id', 'amount', 'sort_order'];

    protected function casts(): array
    {
        return ['amount' => 'float', 'sort_order' => 'integer'];
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }

    public function feeMaster(): BelongsTo
    {
        return $this->belongsTo(FeeMaster::class, 'fee_master_id');
    }
}
