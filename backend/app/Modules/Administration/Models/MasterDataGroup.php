<?php

declare(strict_types=1);

namespace App\Modules\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterDataGroup extends Model
{
    use SoftDeletes;

    protected $table = 'master_data_groups';

    protected $fillable = ['name', 'slug', 'description', 'sort_order', 'is_system'];

    protected function casts(): array
    {
        return ['is_system' => 'boolean', 'sort_order' => 'integer'];
    }

    public function types(): HasMany
    {
        return $this->hasMany(MasterDataType::class, 'group_id')->orderBy('sort_order');
    }
}
