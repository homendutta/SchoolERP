<?php

declare(strict_types=1);

namespace App\Modules\Reports\Models;

use App\Modules\Reports\Enums\ReportFormat;
use App\Modules\Reports\Enums\ScheduleFrequency;
use Illuminate\Database\Eloquent\Model;

/** A scheduled report (queued; optionally emailed via the Communication Engine). */
class ReportSchedule extends Model
{
    protected $table = 'report_schedules';

    protected $fillable = [
        'school_id', 'report_key', 'name', 'frequency', 'format', 'filters',
        'recipients', 'next_run_at', 'last_run_at', 'status', 'created_by',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active', 'format' => 'csv'];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'recipients' => 'array',
            'next_run_at' => 'datetime',
            'last_run_at' => 'datetime',
            'frequency' => ScheduleFrequency::class,
            'format' => ReportFormat::class,
        ];
    }
}
