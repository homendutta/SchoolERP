<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Modules\Finance\Enums\FeePaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One installment in a student fee's payment schedule. */
class FeeInstallment extends Model
{
    protected $table = 'fee_installments';

    protected $fillable = ['school_id', 'student_fee_id', 'name', 'due_date', 'amount', 'paid_amount', 'status', 'sort_order'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'pending'];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount' => 'float',
            'paid_amount' => 'float',
            'sort_order' => 'integer',
            'status' => FeePaymentStatus::class,
        ];
    }

    public function studentFee(): BelongsTo
    {
        return $this->belongsTo(StudentFee::class);
    }
}
