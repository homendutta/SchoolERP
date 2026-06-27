<?php

declare(strict_types=1);

namespace App\Modules\Academic\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Teacher assignment with history: only one row per AY/Class/Section is
 * active at a time; superseded rows are kept (is_active=false, ended_on set).
 */
class ClassTeacher extends Model
{
    protected $fillable = [
        'academic_year_id', 'class_id', 'section_id', 'teacher_id', 'is_active', 'assigned_on', 'ended_on',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'assigned_on' => 'date',
            'ended_on' => 'date',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
