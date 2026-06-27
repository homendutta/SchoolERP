<?php

declare(strict_types=1);

namespace App\Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterDataType extends Model
{
    use SoftDeletes;

    protected $table = 'master_data_types';

    protected $fillable = ['group_id', 'slug', 'name', 'description', 'sort_order', 'is_system'];

    protected function casts(): array
    {
        return ['is_system' => 'boolean', 'sort_order' => 'integer'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(MasterDataGroup::class, 'group_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(MasterDataValue::class, 'type_id')->orderBy('sort_order');
    }
}
