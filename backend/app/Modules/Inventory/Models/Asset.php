<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Enums\AssetCondition;
use App\Modules\Inventory\Enums\AssetStatus;
use App\Platform\Foundation\Identity\Enums\IdentityType;
use App\Platform\Foundation\Identity\Models\Identity;
use App\Platform\Foundation\Media\Models\Media;
use App\Platform\Shared\Traits\HasIdentity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A physical, uniquely identifiable asset. Gets its OWN permanent platform
 * Identity on creation (barcode + QR generated dynamically by the Identity
 * Platform — never stored as images). Asset number from the Number Generator.
 */
class Asset extends Model
{
    use HasIdentity;
    use SoftDeletes;

    protected $table = 'assets';

    protected $fillable = [
        'school_id', 'asset_number', 'identity_id', 'serial_number', 'asset_model_id', 'category_id',
        'vendor_id', 'purchase_date', 'purchase_value', 'current_value', 'condition', 'status', 'photo_media_id',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'available', 'condition' => 'good'];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'purchase_value' => 'float',
            'current_value' => 'float',
            'condition' => AssetCondition::class,
            'status' => AssetStatus::class,
        ];
    }

    public function identityType(): IdentityType
    {
        return IdentityType::Asset;
    }

    public function assetModel(): BelongsTo
    {
        return $this->belongsTo(AssetModel::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /** The asset's own Identity (source of barcode + QR). */
    public function assetIdentity(): BelongsTo
    {
        return $this->belongsTo(Identity::class, 'identity_id');
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_media_id');
    }
}
