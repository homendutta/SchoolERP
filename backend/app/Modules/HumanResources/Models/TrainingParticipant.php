<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Modules\HumanResources\Enums\TrainingParticipantStatus;
use App\Modules\Staff\Models\Staff;
use App\Platform\Foundation\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An employee enrolled on a training programme (certificate is a Media reference). */
class TrainingParticipant extends Model
{
    protected $table = 'hr_training_participants';

    protected $fillable = ['training_id', 'staff_id', 'certificate_media_id', 'status', 'completed_on'];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'assigned'];

    protected function casts(): array
    {
        return [
            'completed_on' => 'date',
            'status' => TrainingParticipantStatus::class,
        ];
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class, 'training_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'certificate_media_id');
    }
}
