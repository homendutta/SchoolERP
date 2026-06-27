<?php

declare(strict_types=1);

namespace App\Modules\Academic\Models;

use App\Modules\Academic\Enums\CalendarEventType;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalendarEvent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'academic_calendar_id', 'holiday_type_id', 'title', 'description',
        'event_type', 'start_date', 'end_date', 'is_recurring', 'status',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => CalendarEventType::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'is_recurring' => 'boolean',
            'status' => RecordStatus::class,
        ];
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(AcademicCalendar::class, 'academic_calendar_id');
    }

    public function holidayType(): BelongsTo
    {
        return $this->belongsTo(HolidayType::class);
    }
}
