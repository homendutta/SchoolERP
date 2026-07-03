<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Configurable designation within a department (supports hierarchy + grade). */
class Designation extends Model
{
    use SoftDeletes;

    protected $table = 'hr_designations';

    protected $fillable = [
        'school_id', 'department_id', 'parent_id', 'name', 'code', 'grade', 'description', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return ['status' => RecordStatus::class];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
