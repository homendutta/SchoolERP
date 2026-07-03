<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Reusable, versioned salary structure (a set of components). History preserved. */
class Structure extends Model
{
    use SoftDeletes;

    protected $table = 'payroll_structures';

    protected $fillable = ['school_id', 'name', 'grade', 'effective_date', 'version', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'version' => 1];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'version' => 'integer',
            'status' => RecordStatus::class,
        ];
    }

    public function components(): HasMany
    {
        return $this->hasMany(StructureComponent::class, 'structure_id')->orderBy('sequence');
    }
}
