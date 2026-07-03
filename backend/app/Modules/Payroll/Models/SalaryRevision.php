<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Models;

use App\Modules\Payroll\Enums\RevisionType;
use App\Modules\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Audit trail of a salary revision; links to the new immutable version produced. */
class SalaryRevision extends Model
{
    protected $table = 'payroll_salary_revisions';

    protected $fillable = [
        'school_id', 'staff_id', 'assignment_id', 'previous_assignment_id', 'structure_id',
        'revision_type', 'effective_date', 'reason', 'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'revision_type' => RevisionType::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(SalaryAssignment::class, 'assignment_id');
    }
}
