<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Models;

use App\Modules\Timetable\Enums\Weekday;
use Illuminate\Database\Eloquent\Model;

/** A school's working-day configuration (one row per weekday). */
class TimetableWorkingDay extends Model
{
    protected $table = 'timetable_working_days';

    protected $fillable = [
        'school_id', 'weekday', 'is_working', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'weekday' => Weekday::class,
            'is_working' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
