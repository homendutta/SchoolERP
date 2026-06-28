<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Modules\Finance\Enums\AdjustmentType;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An independent financial adjustment (credit/debit note, waiver, manual). */
class Adjustment extends Model
{
    protected $table = 'adjustments';

    protected $fillable = [
        'school_id', 'student_id', 'student_fee_id', 'transaction_number',
        'type', 'amount', 'reason', 'status', 'created_by',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'type' => 'manual'];

    protected function casts(): array
    {
        return ['amount' => 'float', 'type' => AdjustmentType::class];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
