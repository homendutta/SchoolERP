<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Modules\Administration\Models\MasterDataValue;
use App\Modules\Finance\Enums\PaymentStatus;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Records what was PAID. Never deleted; refunds/adjustments are separate. */
class Payment extends Model
{
    use SoftDeletes;

    protected $table = 'payments';

    protected $fillable = [
        'school_id', 'student_id', 'receipt_number', 'transaction_number', 'payment_method_id',
        'amount', 'refunded_amount', 'paid_on', 'reference', 'notes', 'gateway', 'status', 'recorded_by',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'completed'];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'refunded_amount' => 'float',
            'paid_on' => 'date',
            'status' => PaymentStatus::class,
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** Payment method is Master Data (never hardcoded). */
    public function method(): BelongsTo
    {
        return $this->belongsTo(MasterDataValue::class, 'payment_method_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }
}
