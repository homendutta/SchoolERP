<?php

declare(strict_types=1);

namespace App\Modules\Staff\Models;

use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffQualification extends Model
{
    protected $table = 'staff_qualifications';

    protected $fillable = [
        'school_id', 'staff_id', 'qualification', 'institution',
        'board_university', 'year', 'grade', 'certificate_media_id',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'certificate_media_id');
    }
}
