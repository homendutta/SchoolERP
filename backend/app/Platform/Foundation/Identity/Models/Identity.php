<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Identity\Models;

use App\Platform\Foundation\Identity\Enums\IdentityStatus;
use App\Platform\Foundation\Identity\Enums\IdentityType;
use App\Platform\Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A permanent person-identity. Never construct directly — go through the
 * IdentityService / Actions. identity_number, public_identifier and owner are
 * immutable once issued.
 */
class Identity extends Model
{
    use HasUuid;

    protected $table = 'identities';

    protected $fillable = [
        'uuid', 'school_id', 'identity_number', 'identity_type',
        'owner_type', 'owner_id', 'public_identifier', 'qr_payload',
        'barcode_value', 'status', 'metadata', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'identity_type' => IdentityType::class,
            'status' => IdentityStatus::class,
            'qr_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function isActive(): bool
    {
        return $this->status === IdentityStatus::Active;
    }
}
