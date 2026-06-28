<?php

declare(strict_types=1);

namespace App\Modules\Students\Models;

use App\Modules\Administration\Models\MasterDataValue;
use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDocument extends Model
{
    protected $table = 'student_documents';

    protected $fillable = [
        'school_id', 'student_id', 'document_type_id', 'media_id',
        'title', 'status', 'remarks', 'uploaded_by',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
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
