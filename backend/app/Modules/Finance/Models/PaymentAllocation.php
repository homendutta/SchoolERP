<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** How a payment was split across fee items / installments. */
class PaymentAllocation extends Model
{
    protected $table = 'payment_allocations';

    protected $fillable = ['school_id', 'payment_id', 'student_fee_item_id', 'installment_id', 'amount'];

    protected function casts(): array
    {
        return ['amount' => 'float'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(StudentFeeItem::class, 'student_fee_item_id');
    }
}
