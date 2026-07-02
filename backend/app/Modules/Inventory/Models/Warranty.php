<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Enums\WarrantyStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An asset warranty. Reminder events go through the Communication Engine. */
class Warranty extends Model
{
    protected $table = 'asset_warranties';

    protected $fillable = ['school_id', 'asset_id', 'vendor_id', 'start_date', 'end_date', 'coverage', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'status' => WarrantyStatus::class];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
