<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Models;

use App\Modules\Administration\Models\MasterDataValue;
use App\Modules\Admissions\Enums\VerificationStatus;
use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionDocument extends Model
{
    protected $fillable = [
        'school_id', 'application_id', 'document_type_id', 'media_id',
        'title', 'status', 'remarks', 'verified_by', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => VerificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }

    /** Document type is Master Data (never hardcoded). */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(MasterDataValue::class, 'document_type_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
