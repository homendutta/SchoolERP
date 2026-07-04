<?php

declare(strict_types=1);

namespace App\Modules\Reports\Models;

use App\Modules\Reports\Enums\ExportStatus;
use App\Modules\Reports\Enums\ReportFormat;
use Illuminate\Database\Eloquent\Model;

/** An export request + its history (queue for large/scheduled exports). */
class ReportExport extends Model
{
    protected $table = 'report_exports';

    protected $fillable = [
        'school_id', 'report_key', 'report_name', 'format', 'status', 'params',
        'row_count', 'media_id', 'error', 'requested_by', 'completed_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'queued', 'row_count' => 0];

    protected function casts(): array
    {
        return [
            'params' => 'array',
            'row_count' => 'integer',
            'completed_at' => 'datetime',
            'format' => ReportFormat::class,
            'status' => ExportStatus::class,
        ];
    }
}
