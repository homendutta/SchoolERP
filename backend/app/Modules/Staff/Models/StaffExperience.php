<?php

declare(strict_types=1);

namespace App\Modules\Staff\Models;

use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffExperience extends Model
{
    protected $table = 'staff_experiences';

    protected $fillable = [
        'school_id', 'staff_id', 'organization', 'designation',
        'from_date', 'to_date', 'reason_for_leaving', 'certificate_media_id',
    ];

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'certificate_media_id');
    }
}
