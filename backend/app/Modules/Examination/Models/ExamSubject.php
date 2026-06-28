<?php

declare(strict_types=1);

namespace App\Modules\Examination\Models;

use App\Modules\Academic\Models\SchoolClass;
use App\Modules\Academic\Models\Section;
use App\Modules\Academic\Models\Subject;
use App\Modules\Administration\Models\MasterDataValue;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Subject mapping for a session (class/section/subject + marks config). */
class ExamSubject extends Model
{
    use SoftDeletes;

    protected $table = 'exam_subjects';

    protected $fillable = [
        'school_id', 'exam_session_id', 'class_id', 'section_id', 'subject_id',
        'subject_type_id', 'is_elective', 'max_marks', 'passing_marks',
        'has_components', 'sort_order', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'is_elective' => 'boolean',
            'has_components' => 'boolean',
            'max_marks' => 'float',
            'passing_marks' => 'float',
            'sort_order' => 'integer',
            'status' => RecordStatus::class,
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /** Subject type (Core / Elective …) is Master Data. */
    public function subjectType(): BelongsTo
    {
        return $this->belongsTo(MasterDataValue::class, 'subject_type_id');
    }

    public function studentSubjects(): HasMany
    {
        return $this->hasMany(ExamStudentSubject::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(ExamMark::class);
    }
}
