<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Models;

use App\Modules\Admissions\Enums\ApprovalStepStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A per-application instance of a workflow step, carrying its own decision.
 */
class AdmissionApprovalStep extends Model
{
    protected $fillable = [
        'school_id', 'application_id', 'workflow_step_id', 'name', 'role_slug',
        'sort_order', 'status', 'actor_id', 'acted_at', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'status' => ApprovalStepStatus::class,
            'acted_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }
}
