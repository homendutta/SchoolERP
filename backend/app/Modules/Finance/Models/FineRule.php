<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Modules\Finance\Enums\FineMode;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Configurable fine rule with grace period and maximum fine. */
class FineRule extends Model
{
    use SoftDeletes;

    protected $table = 'fine_rules';

    protected $fillable = ['school_id', 'name', 'fee_category_id', 'mode', 'amount', 'grace_period_days', 'max_fine', 'status'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'mode' => 'flat'];

    protected function casts(): array
    {
        return [
            'mode' => FineMode::class,
            'amount' => 'float',
            'grace_period_days' => 'integer',
            'max_fine' => 'float',
            'status' => RecordStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class, 'fee_category_id');
    }
}
