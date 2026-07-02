<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A physical verification record. Historical — never overwritten. */
class Verification extends Model
{
    protected $table = 'asset_verifications';

    protected $fillable = ['school_id', 'asset_id', 'status', 'notes', 'verified_by', 'verified_at'];

    protected function casts(): array
    {
        return ['status' => VerificationStatus::class, 'verified_at' => 'datetime'];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
