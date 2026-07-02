<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A consumable stock item. Never individually tracked; never given an Identity. */
class Consumable extends Model
{
    use SoftDeletes;

    protected $table = 'consumables';

    protected $fillable = ['school_id', 'category_id', 'name', 'code', 'unit', 'minimum_stock', 'current_stock', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'unit' => 'unit', 'minimum_stock' => 0, 'current_stock' => 0];

    protected function casts(): array
    {
        return ['minimum_stock' => 'float', 'current_stock' => 'float', 'status' => RecordStatus::class];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest('id');
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }
}
