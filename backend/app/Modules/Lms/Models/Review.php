<?php

declare(strict_types=1);

namespace App\Modules\Lms\Models;

use App\Modules\Lms\Enums\ReviewAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An append-only teacher review of a submission. */
class Review extends Model
{
    protected $table = 'lms_reviews';

    protected $fillable = ['school_id', 'submission_id', 'reviewer_id', 'action', 'comment', 'marks'];

    protected function casts(): array
    {
        return ['marks' => 'decimal:2', 'action' => ReviewAction::class];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }
}
