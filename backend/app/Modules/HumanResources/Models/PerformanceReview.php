<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Modules\HumanResources\Enums\ReviewStatus;
use App\Modules\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A performance review; stored as history and never overwritten. */
class PerformanceReview extends Model
{
    use SoftDeletes;

    protected $table = 'hr_performance_reviews';

    protected $fillable = [
        'school_id', 'staff_id', 'reviewer_id', 'review_period_start', 'review_period_end',
        'goals', 'rating', 'comments', 'development_plan', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'scheduled'];

    protected function casts(): array
    {
        return [
            'review_period_start' => 'date',
            'review_period_end' => 'date',
            'rating' => 'decimal:2',
            'status' => ReviewStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'reviewer_id');
    }
}
