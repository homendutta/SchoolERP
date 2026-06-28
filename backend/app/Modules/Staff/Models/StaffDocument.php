<?php

declare(strict_types=1);

namespace App\Modules\Staff\Models;

use App\Modules\Administration\Models\MasterDataValue;
use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffDocument extends Model
{
    protected $table = 'staff_documents';

    protected $fillable = [
        'school_id', 'staff_id', 'document_type_id', 'media_id',
        'title', 'status', 'remarks', 'uploaded_by',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
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
