<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Enums\DepreciationMethod;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A reusable asset model — a type of asset. Carries depreciation metadata. */
class AssetModel extends Model
{
    use SoftDeletes;

    protected $table = 'asset_models';

    protected $fillable = [
        'school_id', 'category_id', 'name', 'brand', 'manufacturer', 'model_number', 'description',
        'default_warranty_months', 'depreciation_method', 'useful_life_years', 'salvage_value', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'depreciation_method' => 'none'];

    protected function casts(): array
    {
        return [
            'default_warranty_months' => 'integer',
            'depreciation_method' => DepreciationMethod::class,
            'useful_life_years' => 'integer',
            'salvage_value' => 'float',
            'status' => RecordStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }
}
