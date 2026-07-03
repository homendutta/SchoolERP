<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Models;

use App\Modules\Payroll\Enums\LoanStatus;
use App\Modules\Payroll\Enums\LoanType;
use App\Modules\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Employee loan / advance. Payroll deducts installments; Finance owns the cash. */
class Loan extends Model
{
    use SoftDeletes;

    protected $table = 'payroll_loans';

    protected $fillable = [
        'school_id', 'staff_id', 'loan_type', 'reference', 'principal', 'balance',
        'installment_amount', 'disbursed_on', 'approved_by', 'status', 'notes',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'pending', 'loan_type' => 'loan'];

    protected function casts(): array
    {
        return [
            'principal' => 'decimal:2',
            'balance' => 'decimal:2',
            'installment_amount' => 'decimal:2',
            'disbursed_on' => 'date',
            'loan_type' => LoanType::class,
            'status' => LoanStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
