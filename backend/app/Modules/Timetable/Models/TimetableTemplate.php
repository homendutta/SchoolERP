<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Models;

use App\Modules\Academic\Models\AcademicYear;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A named timetable template (Summer / Winter / Exam schedule). */
class TimetableTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'timetable_templates';

    protected $fillable = [
        'school_id', 'academic_year_id', 'name', 'code', 'description', 'is_active', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'status' => RecordStatus::class,
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ClassTimetable::class, 'template_id');
    }
}
