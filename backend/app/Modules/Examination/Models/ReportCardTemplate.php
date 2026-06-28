<?php

declare(strict_types=1);

namespace App\Modules\Examination\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A configurable report-card template (no visual designer). */
class ReportCardTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'report_card_templates';

    protected $fillable = [
        'school_id', 'name', 'code', 'config', 'is_default', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_default' => 'boolean',
            'status' => RecordStatus::class,
        ];
    }
}
