<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Models;

use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Timetable\Enums\SpecialEventType;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A timetable override (Sports Day, Holiday, Exam Week …). Stored separately. */
class TimetableSpecialEvent extends Model
{
    use SoftDeletes;

    protected $table = 'timetable_special_events';

    protected $fillable = [
        'school_id', 'academic_year_id', 'name', 'event_type', 'start_date', 'end_date',
        'scope', 'class_id', 'section_id', 'cancels_classes', 'description', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'active',
        'scope' => 'school',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => SpecialEventType::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'cancels_classes' => 'boolean',
            'status' => RecordStatus::class,
        ];
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
