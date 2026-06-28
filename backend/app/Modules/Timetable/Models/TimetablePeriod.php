<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A configurable period in the bell schedule (incl. breaks). */
class TimetablePeriod extends Model
{
    use SoftDeletes;

    protected $table = 'timetable_periods';

    protected $fillable = [
        'school_id', 'name', 'code', 'start_time', 'end_time', 'sort_order', 'is_break', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_break' => 'boolean',
            'status' => RecordStatus::class,
        ];
    }
}
