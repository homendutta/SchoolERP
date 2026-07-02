<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Models;

use App\Modules\Hostel\Enums\VisitorStatus;
use App\Modules\Students\Models\Student;
use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A hostel visitor. ID proof is a Media reference. */
class Visitor extends Model
{
    protected $table = 'hostel_visitors';

    protected $fillable = [
        'school_id', 'hostel_id', 'student_id', 'visitor_name', 'identity_proof', 'id_media_id',
        'visit_date', 'check_in', 'check_out', 'purpose', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'pending'];

    protected function casts(): array
    {
        return [
            'status' => VisitorStatus::class,
            'visit_date' => 'date',
            'check_in' => 'datetime',
            'check_out' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function idProof(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'id_media_id');
    }
}
