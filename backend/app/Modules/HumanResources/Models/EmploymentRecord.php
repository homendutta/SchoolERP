<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Modules\HumanResources\Enums\EmploymentStatus;
use App\Modules\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One row in an employee's EMPLOYMENT HISTORY. A change of department /
 * designation / type / status closes the current row (is_current = false) and a
 * new row is created — history is never overwritten.
 */
class EmploymentRecord extends Model
{
    use SoftDeletes;

    protected $table = 'hr_employment_records';

    protected $fillable = [
        'school_id', 'staff_id', 'department_id', 'designation_id', 'employment_type',
        'joining_date', 'confirmation_date', 'contract_start', 'contract_end',
        'reporting_manager_id', 'status', 'is_current', 'change_reason', 'notes',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'is_current' => true];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'confirmation_date' => 'date',
            'contract_start' => 'date',
            'contract_end' => 'date',
            'is_current' => 'boolean',
            'status' => EmploymentStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'reporting_manager_id');
    }
}
