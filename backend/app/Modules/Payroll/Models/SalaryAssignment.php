<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Models;

use App\Modules\Payroll\Enums\RevisionType;
use App\Modules\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One immutable salary VERSION assigned to an employee. A revision creates a new
 * version and closes the previous one (is_current) — versions are never
 * overwritten.
 */
class SalaryAssignment extends Model
{
    use SoftDeletes;

    protected $table = 'payroll_salary_assignments';

    protected $fillable = [
        'school_id', 'staff_id', 'structure_id', 'effective_date', 'revision_number',
        'revision_type', 'reason', 'approved_by', 'is_current',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['revision_number' => 1, 'is_current' => true];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'revision_number' => 'integer',
            'is_current' => 'boolean',
            'revision_type' => RevisionType::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class, 'structure_id');
    }
}
