<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Configurable HR department (parent/child hierarchy; code from Number Generator). */
class Department extends Model
{
    use SoftDeletes;

    protected $table = 'hr_departments';

    protected $fillable = ['school_id', 'parent_id', 'name', 'code', 'description', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return ['status' => RecordStatus::class];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class, 'department_id');
    }
}
