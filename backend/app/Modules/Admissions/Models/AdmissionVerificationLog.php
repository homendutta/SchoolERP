<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only verification history entry. Created but never updated.
 */
class AdmissionVerificationLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'school_id', 'application_id', 'document_id', 'from_status', 'to_status', 'remarks', 'actor_id', 'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }
}
