<?php

declare(strict_types=1);

namespace App\Modules\Academic\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicCalendar extends Model
{
    use SoftDeletes;

    protected $fillable = ['school_id', 'academic_year_id', 'name', 'status'];

    protected function casts(): array
    {
        return ['status' => RecordStatus::class];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(CalendarEvent::class)->orderBy('start_date');
    }
}
