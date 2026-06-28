<?php

declare(strict_types=1);

namespace App\Modules\Examination\Models;

use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Configurable marks component (Theory, Practical, Viva…). */
class ExamComponent extends Model
{
    use SoftDeletes;

    protected $table = 'exam_components';

    protected $fillable = [
        'school_id', 'name', 'code', 'sort_order', 'is_active', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'active',
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'status' => RecordStatus::class,
        ];
    }
}
