<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Models;

use App\Modules\Hostel\Enums\HostelFeeType;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A hostel fee definition. Hostel never collects money — Finance does. */
class HostelFee extends Model
{
    use SoftDeletes;

    protected $table = 'hostel_fees';

    protected $fillable = [
        'school_id', 'hostel_id', 'academic_year_id', 'fee_type', 'name', 'amount', 'finance_fee_master_id', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'fee_type' => 'hostel'];

    protected function casts(): array
    {
        return ['fee_type' => HostelFeeType::class, 'amount' => 'float', 'status' => RecordStatus::class];
    }

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }
}
