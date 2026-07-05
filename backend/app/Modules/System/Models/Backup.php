<?php

declare(strict_types=1);

namespace App\Modules\System\Models;

use App\Modules\System\Enums\BackupStatus;
use App\Modules\System\Enums\BackupType;
use Illuminate\Database\Eloquent\Model;

/** A backup manifest (metadata + restore metadata). */
class Backup extends Model
{
    protected $table = 'system_backups';

    protected $fillable = [
        'school_id', 'type', 'status', 'path', 'size', 'manifest', 'checksum',
        'note', 'created_by', 'completed_at', 'verified_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'pending', 'type' => 'full'];

    protected function casts(): array
    {
        return [
            'manifest' => 'array',
            'size' => 'integer',
            'completed_at' => 'datetime',
            'verified_at' => 'datetime',
            'type' => BackupType::class,
            'status' => BackupStatus::class,
        ];
    }
}
