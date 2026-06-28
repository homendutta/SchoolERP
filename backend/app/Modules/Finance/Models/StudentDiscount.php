<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An applied discount or sibling concession on a student fee. */
class StudentDiscount extends Model
{
    protected $table = 'student_discounts';

    protected $fillable = [
        'school_id', 'student_fee_id', 'student_id', 'discount_id', 'sibling_rule_id',
        'source', 'amount', 'reason', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'source' => 'discount'];

    protected function casts(): array
    {
        return ['amount' => 'float'];
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
