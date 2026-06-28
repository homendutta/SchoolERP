<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;

class BiometricDevice extends Model
{
    protected $table = 'biometric_devices';

    protected $fillable = [
        'school_id', 'name', 'device_type', 'device_identifier',
        'location', 'status', 'last_sync_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'last_sync_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
