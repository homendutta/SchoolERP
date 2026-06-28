<?php

declare(strict_types=1);

namespace App\Modules\Examination\Models;

use App\Modules\Academic\Models\AcademicYear;
use App\Modules\Academic\Models\Term;
use App\Modules\Examination\Enums\ExamSessionStatus;
use App\Modules\Examination\Enums\RankingMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** An examination session within an academic year + term. */
class ExamSession extends Model
{
    use SoftDeletes;

    protected $table = 'exam_sessions';

    protected $fillable = [
        'school_id', 'academic_year_id', 'term_id', 'exam_type_id', 'name',
        'start_date', 'end_date', 'status', 'ranking_method', 'description',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'draft',
        'ranking_method' => 'competition',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => ExamSessionStatus::class,
            'ranking_method' => RankingMethod::class,
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(ExamSubject::class);
    }
}
