<?php

declare(strict_types=1);

namespace App\Modules\Payroll\Models;

use App\Modules\Payroll\Enums\ArrearType;
use App\Modules\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A salary / adjustment arrear, applied during payroll processing (historical). */
class Arrear extends Model
{
    use SoftDeletes;

    protected $table = 'payroll_arrears';

    protected $fillable = [
        'school_id', 'staff_id', 'arrear_type', 'amount', 'reason', 'applied', 'applied_run_id',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['arrear_type' => 'salary', 'applied' => false];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'applied' => 'boolean',
            'arrear_type' => ArrearType::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
