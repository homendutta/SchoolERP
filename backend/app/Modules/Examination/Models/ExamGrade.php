<?php

declare(strict_types=1);

namespace App\Modules\Examination\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A single entry in a configurable grading scale. */
class ExamGrade extends Model
{
    use SoftDeletes;

    protected $table = 'exam_grades';

    protected $fillable = [
        'school_id', 'code', 'name', 'min_percentage', 'max_percentage',
        'grade_point', 'remarks', 'is_failing', 'sort_order', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'min_percentage' => 'float',
            'max_percentage' => 'float',
            'grade_point' => 'float',
            'is_failing' => 'boolean',
            'sort_order' => 'integer',
            'status' => RecordStatus::class,
        ];
    }
}
