<?php

declare(strict_types=1);

namespace App\Modules\Examination\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Configurable exam type (Unit Test, Annual, Practical…). */
class ExamType extends Model
{
    use SoftDeletes;

    protected $table = 'exam_types';

    protected $fillable = [
        'school_id', 'name', 'code', 'weightage', 'description', 'sort_order', 'is_active', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'active',
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'weightage' => 'float',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'status' => RecordStatus::class,
        ];
    }
}
