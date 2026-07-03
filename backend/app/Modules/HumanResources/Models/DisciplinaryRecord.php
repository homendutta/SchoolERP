<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Modules\HumanResources\Enums\DisciplinaryAction;
use App\Modules\Staff\Models\Staff;
use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A disciplinary record; complete history is maintained (never overwritten). */
class DisciplinaryRecord extends Model
{
    use SoftDeletes;

    protected $table = 'hr_disciplinary_records';

    protected $fillable = [
        'school_id', 'staff_id', 'action_type', 'incident_date', 'action_date',
        'subject', 'description', 'media_id', 'issued_by', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'action_date' => 'date',
            'action_type' => DisciplinaryAction::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
