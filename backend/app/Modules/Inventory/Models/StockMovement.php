<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Enums\MovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An append-only stock movement. Never overwrites quantities. */
class StockMovement extends Model
{
    public $timestamps = false;

    protected $table = 'stock_movements';

    protected $fillable = ['school_id', 'consumable_id', 'type', 'quantity', 'balance_after', 'reference', 'notes', 'moved_by', 'created_at'];

    protected function casts(): array
    {
        return ['type' => MovementType::class, 'quantity' => 'float', 'balance_after' => 'float', 'created_at' => 'datetime'];
    }

    public function consumable(): BelongsTo
    {
        return $this->belongsTo(Consumable::class);
    }
}
