<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Modules\Finance\Enums\FeePaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A denormalized line item the student owes (never references a live master). */
class StudentFeeItem extends Model
{
    protected $table = 'student_fee_items';

    protected $fillable = [
        'school_id', 'student_fee_id', 'fee_master_id', 'fee_category_id',
        'name', 'amount', 'paid_amount', 'due_date', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'pending'];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'paid_amount' => 'float',
            'due_date' => 'date',
            'status' => FeePaymentStatus::class,
        ];
    }

    public function studentFee(): BelongsTo
    {
        return $this->belongsTo(StudentFee::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class, 'fee_category_id');
    }
}
