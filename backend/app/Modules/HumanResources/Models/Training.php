<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Models;

use App\Modules\HumanResources\Enums\TrainingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A training programme. Training records remain historical. */
class Training extends Model
{
    use SoftDeletes;

    protected $table = 'hr_trainings';

    protected $fillable = [
        'school_id', 'name', 'provider', 'start_date', 'end_date', 'duration_hours', 'description', 'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = ['status' => 'planned'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'duration_hours' => 'decimal:2',
            'status' => TrainingStatus::class,
        ];
    }

    public function participants(): HasMany
    {
        return $this->hasMany(TrainingParticipant::class, 'training_id');
    }
}
