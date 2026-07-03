<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Models;

use App\Modules\Payroll\Enums\ComponentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One earning / deduction / employer line on a payslip. */
class PayslipLine extends Model
{
    protected $table = 'payroll_payslip_lines';

    protected $fillable = ['payslip_id', 'component_id', 'name', 'code', 'line_type', 'amount'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'line_type' => ComponentType::class,
        ];
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class, 'payslip_id');
    }
}
