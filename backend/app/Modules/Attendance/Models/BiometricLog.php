<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Models;

use App\Modules\Attendance\Enums\BiometricProcessingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable raw biometric event. Never deleted — an audit record.
 */
class BiometricLog extends Model
{
    protected $table = 'biometric_logs';

    protected $fillable = [
        'school_id', 'device_id', 'identity_number', 'event_time',
        'direction', 'raw_payload', 'processing_status', 'attendance_id',
    ];

    protected function casts(): array
    {
        return [
            'event_time' => 'datetime',
            'raw_payload' => 'array',
            'processing_status' => BiometricProcessingStatus::class,
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(BiometricDevice::class, 'device_id');
    }
}
