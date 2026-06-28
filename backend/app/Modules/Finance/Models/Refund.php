<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Modules\Finance\Enums\RefundType;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A refund transaction. Never deletes the underlying payment. */
class Refund extends Model
{
    protected $table = 'refunds';

    protected $fillable = [
        'school_id', 'student_id', 'payment_id', 'transaction_number',
        'amount', 'type', 'reason', 'refunded_on', 'status', 'processed_by',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'completed', 'type' => 'partial'];

    protected function casts(): array
    {
        return ['amount' => 'float', 'refunded_on' => 'date', 'type' => RefundType::class];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
