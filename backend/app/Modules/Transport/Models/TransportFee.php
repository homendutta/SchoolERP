<?php

declare(strict_types=1);

namespace App\Modules\Transport\Models;

use App\Modules\Transport\Enums\TransportFeeType;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A transport fee definition. Transport never collects money — Finance does. */
class TransportFee extends Model
{
    use SoftDeletes;

    protected $table = 'transport_fees';

    protected $fillable = [
        'school_id', 'fee_type', 'route_id', 'stop_id', 'academic_year_id',
        'name', 'amount', 'finance_fee_master_id', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'fee_type' => 'route'];

    protected function casts(): array
    {
        return ['fee_type' => TransportFeeType::class, 'amount' => 'float', 'status' => RecordStatus::class];
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function stop(): BelongsTo
    {
        return $this->belongsTo(Stop::class);
    }
}
