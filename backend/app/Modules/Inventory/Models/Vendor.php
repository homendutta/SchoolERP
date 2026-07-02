<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;

    protected $table = 'inventory_vendors';

    protected $fillable = ['school_id', 'name', 'contact', 'gst_number', 'address', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return ['status' => RecordStatus::class];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VendorDocument::class);
    }
}
