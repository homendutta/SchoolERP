<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Configurable leave type (Casual, Sick, Earned, …; nothing hardcoded). */
class LeaveType extends Model
{
    use SoftDeletes;

    protected $table = 'hr_leave_types';

    protected $fillable = ['school_id', 'name', 'code', 'description', 'is_paid', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'status' => RecordStatus::class,
        ];
    }

    public function policies(): HasMany
    {
        return $this->hasMany(LeavePolicy::class, 'leave_type_id');
    }
}
