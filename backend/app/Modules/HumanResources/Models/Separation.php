<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Modules\HumanResources\Enums\ClearanceStatus;
use App\Modules\HumanResources\Enums\SeparationType;
use App\Modules\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Employee separation. The employee is never deleted — this records the exit and
 * a new "separated" employment state is created. Separated employees remain
 * searchable.
 */
class Separation extends Model
{
    use SoftDeletes;

    protected $table = 'hr_separations';

    protected $fillable = [
        'school_id', 'staff_id', 'separation_type', 'last_working_day', 'reason',
        'clearance_status', 'exit_notes', 'initiated_by', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'clearance_status' => 'pending'];

    protected function casts(): array
    {
        return [
            'last_working_day' => 'date',
            'separation_type' => SeparationType::class,
            'clearance_status' => ClearanceStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
