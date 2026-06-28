<?php

declare(strict_types=1);

namespace App\Modules\Examination\Models;

use App\Modules\Examination\Enums\InvigilatorRole;
use App\Modules\Staff\Models\Staff;
use App\Platform\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Invigilator (Staff) assigned to a scheduled exam. */
class ExamInvigilator extends Model
{
    use SoftDeletes;

    protected $table = 'exam_invigilators';

    protected $fillable = [
        'school_id', 'exam_schedule_id', 'staff_id', 'role', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'active',
        'role' => 'assistant',
    ];

    protected function casts(): array
    {
        return [
            'role' => InvigilatorRole::class,
            'status' => RecordStatus::class,
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
