<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Models;

use App\Modules\Payroll\Enums\CalculationType;
use App\Modules\Payroll\Enums\StatutoryType;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Configurable statutory deduction (PF / ESI / PT / TDS / Other). Config only — no hardcoded rates. */
class StatutoryComponent extends Model
{
    use SoftDeletes;

    protected $table = 'payroll_statutory_components';

    protected $fillable = [
        'school_id', 'name', 'code', 'statutory_type', 'calculation_type',
        'employee_rate', 'employer_rate', 'based_on', 'wage_ceiling', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'calculation_type' => 'percentage'];

    protected function casts(): array
    {
        return [
            'employee_rate' => 'decimal:4',
            'employer_rate' => 'decimal:4',
            'wage_ceiling' => 'decimal:2',
            'statutory_type' => StatutoryType::class,
            'calculation_type' => CalculationType::class,
            'status' => RecordStatus::class,
        ];
    }
}
